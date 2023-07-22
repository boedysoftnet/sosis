<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Barang;
use App\Models\Konfigruasi;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PiutangSetoran;
use App\Models\Point;
use Carbon\Carbon;
use Facade\IgnitionContracts\BaseSolution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index()
    {
        $type_transaksi = \request('type_transaksi');
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $userId = \request('user_id');
        $data = Penjualan::query();
        $data->where('perusahaan_id', \request()->user()->perusahaan_id);
        $data->without('user');
        if ($userId) $data->where('user_id', $userId);
        $data->whereNotNull('valid');
        $data->whereDate('created_at', '>=', $tgl_awal);
        $data->whereDate('created_at', '<=', $tgl_akhir);
        $data->whereHas('perusahaan', function ($q) {
            $q->where('owner_id', \request()->user()->perusahaan->owner->id);
        });
        if ($type_transaksi && $type_transaksi != 'Khusus Terhutang') {
            $data->where('status', $type_transaksi);
        } elseif ($type_transaksi == 'Khusus Terhutang') {
            $data->where('piutang', '>', 0);
        }
        return Boedysoft::getJson(200, 'Data Penjualan', collect($data->orderBy('id')->get())->map(function ($row) {
            return [
                'id' => $row->id,
                'customer' => $row->customer->nama,
                'picture_customer' => $row->customer->url,
                'bank' => $row->bank->nama_bank,
                'piutang' => $row->piutang,
                'tgl_tempo' => $row->tgl_tempo,
                'created_at' => $row->created_at,
                'waktu' => $row->created_at->format('H:i'),
                'sub_total' => $row->sub_total,
                'diskon' => $row->diskon,
                'total' => $row->total,
                'ppn' => $row->ppn,
                'user' => $row->user->name,
                'sisa_piutang' => $row->sisa_piutang,
                'total_setelah_ppn' => $row->total_setelah_ppn,
                'status' => $row->status,
            ];
        }));
    }

    public function newPenjualan($id = null)
    {
        if ($id) {
            $data = Penjualan::find($id);
            return Boedysoft::getJson(200, 'Penjualan Berhasil dibuat..!', $data);
        } else {
            $data = Penjualan::whereNull('valid')->whereUserId(\request()->user()->id);
            if ($data->count() > 0) {
                return Boedysoft::getJson(200, 'Penjualan Berhasil dibuat..!', $data->first());
            } else {
                $data = Penjualan::create([
                    'user_id' => \request()->user()->id,
                    'customer_id' => 1,
                    'bank_id' => 1,
                    'perusahaan_id' => \request()->user()->perusahaan_id,
                ]);
                return Boedysoft::getJson(200, 'Penjualan Berhasil dibuat..!', Penjualan::find($data->id));
            }
        }
    }

    public function addList(Penjualan $penjualan, Request $request)
    {
        $ppn = Boedysoft::getKonfigurasiSystem('pungutan ppn');
        $penjualan->penjualanDetail()->create(array_merge($request->all(), [
            'barang_id' => $request->id,
            'harga' => $request->harga_jual,
            'qty' => $request->qty * $request->satuan_isi,
            'ppn' => ($request->harga_jual * $request->qty) * $ppn
        ]));
        return Boedysoft::getJson(200);
    }

    public function destroy(Penjualan $penjualan)
    {
        return DB::transaction(function () use ($penjualan) {
            if ($penjualan->status === 'Finish') {
                foreach ($penjualan->penjualanDetail as $item) {
                    $sisa = Boedysoft::getStock($item->barang_id) + $item->qty;
                    if ($sisa < 0) {
                        DB::rollBack();
                        return Boedysoft::getJson(201, "Tidak dapat dihapus, {$item->barang->nama_barang} sudah digunakan..!");
                    } else {
                        Boedysoft::setStock($item->barang_id, $item->qty);
                    }
                }
                $penjualan->jurnal()->delete();
                Boedysoft::setHistoryUser($penjualan, "Menghapus transaksi penjualan seniali : {$penjualan->total_setelah_ppn} pada supplier {$penjualan->customer->nama}");
                $penjualan->forceDelete();
                return Boedysoft::getJson(200, 'Pembelian berhasil dihapus..!');
            } else {
                $penjualan->forceDelete();
                return Boedysoft::getJson(200, 'Pembelian berhasil dihapus..!');
            }
        });
    }

    public function destoryItem(PenjualanDetail $penjualanDetail)
    {
        $penjualanDetail->delete();
        return Boedysoft::getJson(200);
    }

    public function editItemPenjualan(PenjualanDetail $penjualanDetail, Request $request)
    {
        $ppn = Boedysoft::getKonfigurasiSystem('pungutan ppn');
        $barang = Barang::find($request->barang_id);
        $barang_satuan = $barang->barangSatuan()->where('satuan', $request->satuan)->first();
        $jumlah = $request->qty * $barang_satuan->isi;
        if ($jumlah <= Boedysoft::getStock($barang->id)) {
            if ($barang_satuan) {
                $penjualanDetail->update([
                    'qty' => $jumlah,
                    'harga' => $barang_satuan->harga,
                    'ppn' => ($request->qty * $barang_satuan->harga) * $ppn,
                    'satuan_isi' => $barang_satuan->isi,
                    'satuan' => $barang_satuan->satuan
                ]);
                return Boedysoft::getJson(200, 'Sukses Edit Data Jumlah..!');
            }
        } else {
            return Boedysoft::getJson(201, 'Maaf Jumlah stock tidak mencukupi..!');
        }
    }

    public function setDiskonGlobal(Penjualan $penjualan, Request $request)
    {
        return DB::transaction(function () use ($penjualan, $request) {
            $ppn = Boedysoft::getKonfigurasiSystem('pungutan ppn');
            $jenis = $request->jenisDiskon;
            $persentase = $request->diskonGlobal * 100 / $penjualan->sub_total;
            foreach ($penjualan->penjualanDetail as $item) {
                $nilai = $item->sub_total * $request->diskonGlobal / 100;
                if ($jenis == 'nominal') $nilai = $item->sub_total * $persentase / 100;
                $item->diskon = -$nilai;
                $item->ppn = $item->total * $ppn;
                $item->save();
            }
            return Boedysoft::getJson(200, 'Sukses menambahkan diskon secara global..!');
        });
    }

    public function simpanPreorder(Penjualan $penjualan)
    {
        return DB::transaction(function () use ($penjualan) {
            $penjualan->update([
                'valid' => Carbon::now(),
                'catatan' => \request('catatan')
            ]);
            Boedysoft::setHistoryUser($penjualan, "{$penjualan->user->name} membuat transaksi preorder untuk : {$penjualan->customer->nama} senilai : Rp. {$penjualan->total_setelah_ppn}");
            return Boedysoft::getJson(200, 'Preorder Berhasil disimpan..!');
        });
    }

    public function show(Penjualan $penjualan)
    {
        return Boedysoft::getJson(200, null, $penjualan);
    }

    public function setCusotmer(Penjualan $penjualan)
    {
        $penjualan->update([
            'customer_id' => \request('customer_id')
        ]);
        return Boedysoft::getJson(200, 'Sukses mengubah customer..!');
    }

    public function simpanPenjualan(Penjualan $penjualan, Request $request)
    {
        return DB::transaction(function ($q) use ($penjualan, $request) {
            $total = $penjualan->total_setelah_ppn;
            $jumlah_uang = $request->uang_tunai + $request->dana_transfer;
            $tunai = $request->uang_tunai;
            $piutang = $total - $jumlah_uang;
            if (!$request->status_piutang) $piutang = 0;
            if ($request->bank_id == 1) {
                if ($total <= $tunai) $tunai = $total;
            }
            $penjualan->update([
                'valid' => Carbon::now(),
                'created_at' => $request->created_at,
                'bank_id' => $request->bank_id,
                'uang_tunai' => $tunai,
                'dana_transfer' => $request->dana_transfer,
                'jumlah_uang' => $jumlah_uang,
                'status' => 'Finish',
                'piutang' => $piutang,
                'catatan' => $request->catatan,
                'ppn_persentase' => Boedysoft::getKonfigurasiSystem('pungutan ppn'),
                'tgl_tempo' => $request->tgl_tempo
            ]);
            $penjualan->created_at = now();
            $penjualan->updated_at = now();
            $penjualan->timestamps = false;
            $penjualan->save();
            $pointKelipatan = Konfigruasi::whereSetting('point kelipatan')->first()->value;
            $penjualan->point()->create([
                'user_id' => Auth::id(),
                'perusahaan_id' => Auth::user()->perusahaan_id,
                'jenis' => 'perolehan',
                'total' => $total,
                'nilai_kelipatan' => $pointKelipatan,
                'point' => round(($total / $pointKelipatan)-0.49),
                'keterangan' => 'Transaksi penjualan',
            ]);
            if ($piutang > 0) {
                Boedysoft::setHistoryUser($penjualan, "{$penjualan->user->name} membaut transaksi penjualan tempo senilai : {$penjualan->total_setelah_ppn} dibayar dimuka {$jumlah_uang} untuk customer {$penjualan->customer->nama} jatuh tempo diberikan : {$penjualan->tgl_tempo}");
            } else {
                Boedysoft::setHistoryUser($penjualan, "{$penjualan->user->name} membaut transaksi penjualan senilai : {$penjualan->total_setelah_ppn} untuk customer {$penjualan->customer->nama}");
            }
            $this->jurnalProses($penjualan, 0, $tunai);
            $this->stockProses($penjualan);
            $this->newPenjualan();
            return Boedysoft::getJson(200, 'Sukses menyimpan penjualan..!', $penjualan);
        });
    }

    public function stockProses(Penjualan $penjualan)
    {
        foreach ($penjualan->penjualanDetail as $item) {
            Boedysoft::setStockHistory($penjualan, "Transaksi Penjualan", $item->barang_id, $item->qty, "Transaksi penjualan kepada : {$penjualan->customer->nama} oleh {$penjualan->user->name}");
            Boedysoft::setStock($item->barang_id, -$item->qty);
        }
    }

    public function jurnalProses(Penjualan $penjualan, $piutang, $tunai)
    {
        /*todo check kembali apaila ada customer yang mengembalkan barang dan nilai nya menjadi negative pada transaksi jurnal masih belum sesuai semangat yach*/
        $piutang = $penjualan->piutang;
        $tempo = $penjualan->tgl_tempo;
        if ($penjualan->total_setelah_ppn > 0) {
            if ($piutang > 0) {
                if ($penjualan->bank_id == 1) {
                    $catatan = "Transaksi penjualan secara kredit kepada {$penjualan->customer->nama} senilai : Rp. {$penjualan->total_setelah_ppn} dengan tempo pembayaran hingga {$penjualan->tgl_tempo}";
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Piutang Customer", $piutang, 0, $catatan);
                    if ($tunai > 0) Boedysoft::setJurnal($penjualan, "Transaksi Penjualan", $penjualan->bank->akun_perkiraan_id, $tunai, 0, $catatan);
                    if ($penjualan->diskon < 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Potongan Untuk Customer", abs($penjualan->diskon), 0, $catatan);
                    if ($penjualan->ppn > 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "PPN Keluaran", 0, $penjualan->ppn, $catatan);
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Penjualan Barang", 0, $penjualan->sub_total, $catatan);

                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Beban HPP", $penjualan->total_pokok, 0, $catatan);
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Persediaan Barang", 0, $penjualan->total_pokok, $catatan);
                } else {
                    $catatan = "Transaksi penjualan secara kredit kepada {$penjualan->customer->nama} senilai : Rp. {$penjualan->total_setelah_ppn} dengan tempo pembayaran hingga {$penjualan->tgl_tempo}";
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Piutang Customer", $piutang, 0, $catatan);
                    if (\request('uang_tunai') > 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Kas Perusahaan", \request('uang_tunai'), 0, $catatan);
                    Boedysoft::setJurnal($penjualan, "Transaksi Penjualan", $penjualan->bank->akun_perkiraan_id, \request('dana_transfer'), 0, $catatan);
                    if ($penjualan->diskon < 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Potongan Untuk Customer", abs($penjualan->diskon), 0, $catatan);
                    if ($penjualan->ppn > 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "PPN Keluaran", 0, $penjualan->ppn, $catatan);
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Penjualan Barang", 0, $penjualan->sub_total, $catatan);

                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Beban HPP", $penjualan->total_pokok, 0, $catatan);
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Persediaan Barang", 0, $penjualan->total_pokok, $catatan);
                }
            } else {
                if ($penjualan->bank_id == 1) {
                    $catatan = "Transaksi penjualan secara tunai kepada {$penjualan->customer->nama} senilai : Rp. {$penjualan->total_setelah_ppn}";
                    Boedysoft::setJurnal($penjualan, "Transaksi Penjualan", $penjualan->bank->akun_perkiraan_id, $penjualan->total_setelah_ppn, 0, $catatan);
                    if ($penjualan->diskon < 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Potongan Untuk Customer", abs($penjualan->diskon), 0, $catatan);
                    if ($penjualan->ppn > 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "PPN Keluaran", 0, $penjualan->ppn, $catatan);
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Penjualan Barang", 0, $penjualan->sub_total, $catatan);

                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Beban HPP", $penjualan->total_pokok, 0, $catatan);
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Persediaan Barang", 0, $penjualan->total_pokok, $catatan);
                } else {
                    $catatan = "Transaksi penjualan secara transfer kepada {$penjualan->customer->nama} senilai : Rp. {$penjualan->total_setelah_ppn}";
                    if (\request('uang_tunai') > 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Kas Perusahaan", \request('uang_tunai'), 0, $catatan);
                    Boedysoft::setJurnal($penjualan, "Transaksi Penjualan", $penjualan->bank->akun_perkiraan_id, \request('dana_transfer'), 0, $catatan);
                    if ($penjualan->diskon < 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Potongan Untuk Customer", abs($penjualan->diskon), 0, $catatan);
                    if ($penjualan->ppn > 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "PPN Keluaran", 0, $penjualan->ppn, $catatan);
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Penjualan Barang", 0, $penjualan->sub_total, $catatan);

                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Beban HPP", $penjualan->total_pokok, 0, $catatan);
                    Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Persediaan Barang", 0, $penjualan->total_pokok, $catatan);
                }
            }
        } else {
            if ($penjualan->bank_id == 1) {
                $catatan = "Transaksi retur penjualan secara tunai kepada {$penjualan->customer->nama} senilai : Rp. {$penjualan->total_setelah_ppn}";
                if ($penjualan->ppn < 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "PPN Keluaran", abs($penjualan->ppn), 0, $catatan);
                Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Penjualan Barang", abs($penjualan->sub_total), 0, $catatan);
                Boedysoft::setJurnal($penjualan, "Transaksi Penjualan", $penjualan->bank->akun_perkiraan_id, 0, abs($penjualan->total_setelah_ppn), $catatan);
                if ($penjualan->diskon < 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Potongan Untuk Customer", 0, abs($penjualan->diskon), $catatan);

                Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Persediaan Barang", abs($penjualan->total_pokok), 0, $catatan);
                Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Beban HPP", 0, abs($penjualan->total_pokok), $catatan);
            } else {
                $catatan = "Transaksi penjualan retur secara transfer kepada {$penjualan->customer->nama} senilai : Rp. {$penjualan->total_setelah_ppn}";
                if ($penjualan->ppn < 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "PPN Keluaran", abs($penjualan->ppn), 0, $catatan);
                Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Penjualan Barang", abs($penjualan->sub_total), 0, $catatan);
                if (\request('uang_tunai') < 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Kas Perusahaan", 0, abs(\request('uang_tunai')), $catatan);
                Boedysoft::setJurnal($penjualan, "Transaksi Penjualan", $penjualan->bank->akun_perkiraan_id, 0, abs(\request('dana_transfer')), $catatan);
                if ($penjualan->diskon < 0) Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Potongan Untuk Customer", 0, abs($penjualan->diskon), $catatan);

                Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Persediaan Barang", $penjualan->total_pokok, 0, $catatan);
                Boedysoft::setJurnalWithParameter($penjualan, "Transaksi Penjualan", "Beban HPP", 0, $penjualan->total_pokok, $catatan);
            }
        }
    }

    public function itemTerjual()
    {
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $data = Barang::query();
        $data->whereHas('penjualanDetail', function ($q) use ($tgl_awal, $tgl_akhir) {
            $q->whereHas('penjualan', function ($q) use ($tgl_awal, $tgl_akhir) {
                $q->whereDate('created_at', '>=', $tgl_awal);
                $q->whereDate('created_at', '<=', $tgl_akhir);
                $q->where('status', 'finish');
            });
        });
        return Boedysoft::getJson(200, null, collect($data->get())->map(function ($row) use ($tgl_awal, $tgl_akhir) {
            $info = $row->penjualanDetail()->whereHas('penjualan', function ($q) use ($tgl_awal, $tgl_akhir) {
                $q->whereDate('created_at', '>=', $tgl_awal);
                $q->whereDate('created_at', '<=', $tgl_akhir);
                $q->where('status', 'finish');
            });
            $jumlah = $info->sum('qty');
            $harga = $info->avg(DB::raw('harga/satuan_isi'));
            $total = $info->sum(DB::raw('qty*(harga/satuan_isi)'));
            return [
                'id' => $row->id,
                'nama_barang' => $row->nama_barang,
                'kategori' => $row->barangKategori->kategori,
                'harga' => $harga,
                'satuan' => $row->satuan,
                'jumlah' => $jumlah,
                'total' => $total,
            ];
        }));
    }

    //bank_id: 1
    //catatan: "asfd"
    //jumlah: 121
    //penerima: "asfda"
    //penjualan_id: 44
    //tgl_setoran: "2022-07-26T06:39"
    public function setoranPiutang(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $penjualan = Penjualan::find($request->penjualan_id);
            $setoran = $penjualan->piutangSetoran()->create([
                'user_id' => $request->user()->id,
                'bank_id' => $request->bank_id,
                'perusahaan_id' => $request->user()->perusahaan_id,
                'jumlah' => $request->jumlah,
                'penerima' => $request->penerima,
                'catatan' => $request->catatan,
                'tgl_setoran' => $request->tgl_setoran,
            ]);
            Boedysoft::setHistoryUser($penjualan, "Penerimaan pembayaran piutang dari {$penjualan->customer->nama} senilai : Rp. {$request->jumlah}");
            $this->transaksiJurnal($penjualan, $setoran, $request);
            return Boedysoft::getJson(200, $request->all());
        });
    }

    public function transaksiJurnal(Penjualan $penjualan, PiutangSetoran $piutangSetoran, Request $request)
    {
        $catatan = "Penerimaan setoran piutang oleh {$penjualan->customer->nama} senilai Rp.{$request->jumlah}";
        Boedysoft::setJurnal($penjualan, "Setoran Piutang", $piutangSetoran->bank->akun_perkiraan_id, $request->jumlah, 0, $catatan, $request->tgl_setoran);
        Boedysoft::setJurnalWithParameter($penjualan, "Setoran Piutang", 'Piutang Customer', 0, $request->jumlah, $catatan, $request->tgl_setoran);
    }

    public function piutangHistorySetoran()
    {
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $data = PiutangSetoran::query();
        $data->whereHas('perusahaan', function ($q) {
            $q->where('owner_id', \request()->user()->perusahaan->owner->id);
        });
        $data->whereDate('created_at', '>=', $tgl_awal);
        $data->whereDate('created_at', '<=', $tgl_akhir);
        return Boedysoft::getJson(200, null, collect($data->get())->map(function ($row) {
            return [
                'id' => $row->id,
                'unit_setor' => $row->perusahaan->nama,
                'nama' => $row->penjualan->customer->nama,
                'alamat' => $row->penjualan->customer->alamat,
                'telpon' => $row->penjualan->customer->telpon,
                'jenis_pembayaran' => $row->bank->nama_bank,
                'jumlah' => $row->jumlah,
                'penerima' => $row->penerima,
                'catatan' => $row->catatan,
                'tgl_setoran' => $row->tgl_setoran
            ];
        }));
    }

    public function statistikBarang()
    {
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $data = Penjualan::query();
        $data->whereDate('created_at', '>=', $tgl_awal);
        $data->whereDate('created_at', '<=', $tgl_akhir);
        $dataFilter = $data->orderBy('created_at')->groupByRaw('date(created_at)')->orderBy('created_at')->get();
        return Boedysoft::getJson(200, null, collect($dataFilter)->map(function ($row) {
            $jumlah = PenjualanDetail::whereHas('penjualan', function ($q) use ($row) {
                $q->whereDate('created_at', $row['created_at']);
            })->sum(DB::raw('(harga*qty)+diskon'));
            return [
                'id' => $row['id'],
                'nama' => $row['created_at']->format('d-M-Y'),
                'jumlah' => (double)$jumlah
            ];
        }));
    }


}
