<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\HutangSetoran;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PembelianController extends Controller
{
    public $rules = [
        'supplier_id' => 'required',
        'perusahaan_id' => 'required',
        'bank_id' => 'required',
        'nomor_faktur' => 'required',
    ];

    public function index()
    {
        $type_transaksi = \request('type_transaksi');
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $data = Pembelian::query();
        $data->where('perusahaan_id',\request()->user()->perusahaan_id);
        $data->without('user');
        $data->whereNotNull('valid');
        $data->whereHas('perusahaan',function ($q){
            $q->where('owner_id',\request()->user()->perusahaan->owner->id);
        });
        $data->whereDate('created_at', '>=', $tgl_awal);
        $data->whereDate('created_at', '<=', $tgl_akhir);
        if ($type_transaksi && $type_transaksi != 'Khusus Terhutang') {
            $data->where('status', $type_transaksi);
        } else {
            $data->where('hutang', '>', 0);
        }
        return Boedysoft::getJson(200, 'Data Pembelian', $data->orderBy('id')->get());
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), $this->rules);

        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            return DB::transaction(function () use ($request) {
                $data = Pembelian::create(array_merge($request->all(), ['user_id' => $request->user()->id]));
                return Boedysoft::getJson(200, 'Data Pembelian berhasil dibuat..!', $data);
            });
        }
    }

    public function update(Request $request, $id)
    {
        $valid = Validator::make($request->all(), $this->rules);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            return DB::transaction(function () use ($request, $id) {
                $data = Pembelian::find($id);
                $data->update(array_merge($request->all(), ['user_id' => $request->user()->id]));
                return Boedysoft::getJson(200, 'Data Pembelian berhasil edit..!', $data);
            });
        }
    }

    public function show(Pembelian $pembelian)
    {
        return Boedysoft::getJson(200, 'Informasi', $pembelian);
    }

    public function destroy(Pembelian $pembelian)
    {
        return DB::transaction(function () use ($pembelian) {
            if ($pembelian->status === 'Finish') {
                foreach ($pembelian->pembelianDetail as $item) {
                    $sisa = Boedysoft::getStock($item->barang_id) - $item->qty;
                    if ($sisa < 0) {
                        DB::rollBack();
                        return Boedysoft::getJson(201, "Tidak dapat dihapus, {$item->barang->nama_barang} sudah digunakan..!");
                    } else {
                        Boedysoft::setStock($item->barang_id, -$item->qty);
                    }
                }
                $pembelian->jurnal()->delete();
                Boedysoft::setHistoryUser($pembelian, "Menghapus transaksi pembelian seniali : {$pembelian->total_setelah_ppn} pada supplier {$pembelian->supplier->nama}");
                $pembelian->forceDelete();
                return Boedysoft::getJson(200, 'Pembelian berhasil dihapus..!');
            } else {
                $pembelian->forceDelete();
                return Boedysoft::getJson(200, 'Pembelian berhasil dihapus..!');
            }
        });
    }

    public function getPembelianDetail(Pembelian $pembelian)
    {
        return Boedysoft::getJson(200, null, $pembelian->pembelianDetail);
    }

    public function detailRemove(PembelianDetail $pembelianDetail)
    {
        $pembelianDetail->delete();
        return Boedysoft::getJson(200, 'Sukses menghapus data ?');
    }

    public function addBarangDetail(Pembelian $pembelian, Request $request)
    {
        $ppn = $pembelian->supplier->ppn;
        $total = $request->qty * $request->harga;
        $nilaiPPN = $total * $ppn / 100;
        $pembelian->pembelianDetail()->create(array_merge($request->all(), ['ppn' => $nilaiPPN,'satuan_isi'=>$request->satuan_isi]));
        return Boedysoft::getJson(200, 'sukses menambahkan barang');
    }

    public function setDiskonGlobal(Pembelian $pembelian, Request $request)
    {
        return DB::transaction(function () use ($pembelian, $request) {
            $persentase = $request->diskonGlobal * 100 / $pembelian->sub_total;
            if ($request->jenisDiskon == '%') Boedysoft::setHistoryUser($pembelian, "Menambahkan diskon global sebesar {$request->diskonGlobal}%");
            if ($request->jenisDiskon == 'nominal') Boedysoft::setHistoryUser($pembelian, "Menambahkan diskon senilai {$request->diskonGlobal}");
            foreach ($pembelian->pembelianDetail as $item) {
                $diskon = $item->sub_total * $request->diskonGlobal / 100;
                if ($request->jenisDiskon == 'nominal') $diskon = $item->sub_total * $persentase / 100;
                $item->update([
                    'diskon' => -$diskon
                ]);
            }
            $request['supplier_id'] = $pembelian->supplier_id;
            $this->setSupplier($pembelian, $request);
            return Boedysoft::getJson(200, 'Sukses menambahkan diskon..!');
        });
    }

    public function setSupplier(Pembelian $pembelian, Request $request)
    {
        return DB::transaction(function () use ($pembelian, $request) {
            $supplier = Supplier::find($request->supplier_id);
            $pembelian->update(['supplier_id' => $supplier->id]);
            foreach ($pembelian->pembelianDetail as $item) {
                $ppn = $item->total * $supplier->ppn / 100;
                $item->update([
                    'ppn' => $ppn
                ]);
            }
            return Boedysoft::getJson(200, 'Sukses penerapan supplier..!');
        });
    }

    public function editDetailPembelian(PembelianDetail $pembelianDetail, Request $request)
    {
        return DB::transaction(function () use ($pembelianDetail, $request) {
            $satuan = BarangSatuan::whereBarangId($pembelianDetail->barang_id)->whereSatuan($request->satuan)->wherePerusahaanId($request->user()->perusahaan_id)->first();
            if ($satuan) {
                $qty = $request->qty * $satuan->isi;
                $pembelianDetail->update(['qty' => $qty]);
            }
            return Boedysoft::getJson(200, 'Sukses penerapan edit items..!');
        });
    }

    public function editHarga(PembelianDetail $pembelianDetail, Request $request)
    {
        return DB::transaction(function () use ($pembelianDetail, $request) {
            $ppn = $pembelianDetail->pembelian->supplier->ppn;
            $biayaPPN = $pembelianDetail->total * $ppn / 100;
            $pembelianDetail->update(['harga' => $request->harga, 'ppn' => $biayaPPN]);
            return Boedysoft::getJson(200, 'Sukses penerapan edit harga..!');
        });
    }

    public function simpanPreorder(Pembelian $pembelian)
    {
        return DB::transaction(function () use ($pembelian) {
            $pembelian->update([
                'valid' => now()
            ]);
            Boedysoft::setHistoryUser($pembelian, "Membaut draf preorder kepada {$pembelian->supplier->nama} dengan total : {$pembelian->total_setelah_ppn}");
            return Boedysoft::getJson(200, 'Sukses Menyimpan draf pembelian..!, Anda dapat memprosesnya dilain waktu');
        });
    }

    public function simpanPembelian(Pembelian $pembelian, Request $request)
    {
        return DB::transaction(function () use ($pembelian, $request) {
            $hutang = 0;
            $jumlahUang = $request->uang_tunai + $request->dana_transfer;
            $pembelian->update([
                'valid' => now(),
                'bank_id' => $request->bank_id,
                'uang_tunai' => $request->uang_tunai,
                'dana_transfer' => $request->dana_transfer,
                'catatan' => $request->catatan,
                'no_faktur' => $request->no_faktur,
                'status' => 'Finish'
            ]);
            $pembelian = Pembelian::find($pembelian->id);
            if ($request->status_hutang) {
                if ($jumlahUang >= $pembelian->total_setelah_ppn) {
                    return Boedysoft::getJson(201, 'Maaf Pembayaran Anda Lunas..!');
                } else {
                    $hutang = $pembelian->total_setelah_ppn - $jumlahUang;
                    $pembelian->update([
                        'hutang' => $hutang
                    ]);
                    $this->jurnalPembelianTempo($pembelian, $request->uang_tunai, $request->dana_transfer, $hutang);
                }
            }
            if ($request->tgl_tempo) $pembelian->update(['tgl_tempo' => $request->tgl_tempo]);
            foreach ($pembelian->pembelianDetail as $item) {
                Boedysoft::setStockHistory($item, 'Stock Masuk', $item->barang_id, $item->qty, "Persediaan masuk melalui transaksi pembelian pada : {$pembelian->supplier->nama} senilai {$pembelian->sub_total}");
                Boedysoft::setHargaNet($item->barang_id, $item->qty, $item->harga);
                Boedysoft::setStock($item->barang_id, $item->qty);
            }
            if ($hutang == 0) $this->jurnalPembelianTunai($pembelian, $request->uang_tunai, $request->dana_transfer);
            Boedysoft::setHistoryUser($pembelian, "Menyimpan pembelian senilai : {$pembelian->total_setelah_ppn} dan menambahkan stock dari supplier : {$pembelian->supplier->nama}..!");
            return Boedysoft::getJson(200, 'Sukses Menyimpan Pembelian..!',$pembelian->pembelianDetail);
        });
    }

    public function jurnalPembelianTunai($pembelian, $tunai, $transfer)
    {
        if ($pembelian->total_setelah_ppn > 0) {
            $kategori = "Pembayaran Tagihan Supplier";
            $catatan = "Pembayaran atas barang yang diterima dari supplier {$pembelian->supplier->nama} dibayar tunai senilai {$pembelian->sub_total}";
            Boedysoft::setJurnalWithParameter($pembelian, $kategori, "persediaan barang", $pembelian->sub_total, 0, $catatan);
            if ($pembelian->ppn > 0) Boedysoft::setJurnalWithParameter($pembelian, $kategori, "ppn masukan", $pembelian->ppn, 0, $catatan);
            if ($pembelian->diskon != 0) Boedysoft::setJurnalWithParameter($pembelian, $kategori, "potongan dari supplier", 0, abs($pembelian->diskon), $catatan);
            if ($tunai > 0) Boedysoft::setJurnalWithParameter($pembelian, $kategori, "kas perusahaan", 0, $tunai, $catatan);
            if ($transfer > 0) Boedysoft::setJurnal($pembelian, $kategori, $pembelian->bank->akun_perkiraan_id, 0, $transfer, $catatan);
        } else {
            $kategori = "Retur Tagihan Supplier";
            $catatan = "Retur atas barang yang diterima dari supplier {$pembelian->supplier->nama} dibayar tunai senilai {$pembelian->sub_total}";
            if ($pembelian->diskon != 0) Boedysoft::setJurnalWithParameter($pembelian, $kategori, "potongan dari supplier", abs($pembelian->diskon), 0, $catatan);
            if ($tunai < 0) Boedysoft::setJurnalWithParameter($pembelian, $kategori, "kas perusahaan", abs($tunai), 0, $catatan);
            if ($transfer < 0) Boedysoft::setJurnal($pembelian, $kategori, $pembelian->bank->akun_perkiraan_id, abs($transfer), 0, $catatan);
            Boedysoft::setJurnalWithParameter($pembelian, $kategori, "persediaan barang", 0, abs($pembelian->sub_total), $catatan);
            if ($pembelian->ppn < 0) Boedysoft::setJurnalWithParameter($pembelian, $kategori, "ppn masukan", 0, abs($pembelian->ppn), $catatan);
        }
    }

    public function jurnalPembelianTempo($pembelian, $tunai, $transfer, $hutang)
    {
        $kategori = "Pembayaran Tagihan Supplier";
        $catatan = "Pembayaran atas barang yang diterima dari supplier {$pembelian->supplier->nama} dibayar tempo s/d {$pembelian->tgl_tempo} senilai {$pembelian->sub_total}";
        Boedysoft::setJurnalWithParameter($pembelian, $kategori, "persediaan barang", $pembelian->sub_total, 0, $catatan);
        if ($pembelian->ppn > 0) Boedysoft::setJurnalWithParameter($pembelian, $kategori, "ppn masukan", $pembelian->ppn, 0, $catatan);
        if ($pembelian->diskon != 0) Boedysoft::setJurnalWithParameter($pembelian, $kategori, "potongan dari supplier", 0, abs($pembelian->diskon), $catatan);
        if ($tunai > 0) Boedysoft::setJurnalWithParameter($pembelian, $kategori, "kas perusahaan", 0, $tunai, $catatan);
        if ($transfer > 0) Boedysoft::setJurnal($pembelian, $kategori, $pembelian->bank->akun_perkiraan_id, 0, $transfer, $catatan);
        Boedysoft::setJurnalWithParameter($pembelian, $kategori, "hutang pada supplier", 0, $hutang, $catatan);
    }

    public function createPembelian()
    {
        $data = Pembelian::query();
        $data->where('user_id', \request()->user()->id);
        $data->whereNull('valid');
        if ($data->count() > 0) {
            return Boedysoft::getJson(200, 'Lanjutkan', $data->first());
        } else {
            $data = \request()->user()->pembelian()->create([
                'supplier_id' => 1,
                'perusahaan_id' => \request()->user()->perusahaan_id,
                'bank_id' => 1,
            ]);
            return Boedysoft::getJson(200, 'Sukses membuat pembelian baru', $data);
        }
    }


    public function getBarangMasuk()
    {
        $cari = \request('cari');
        $limit = \request('limit');
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $data = Barang::query();
        if ($tgl_awal && $tgl_akhir) $data->whereHas('pembelianDetail', function ($q) use ($tgl_awal, $tgl_akhir) {
            $q->whereHas('pembelian', function ($q) use($tgl_awal,$tgl_akhir) {
                $q->whereDate('created_at', '>=', $tgl_awal);
                $q->whereDate('created_at', '<=', $tgl_akhir);
                $q->where('status', 'finish');
            });
        });
        return Boedysoft::getJson(200, 'Data Kategori Barang', collect($data->with('pembelianDetail')->get())->map(function($row){
            return [
                'id'=>$row['id'],
                'nama_barang'=>$row['nama_barang'],
                'harga_terakhir'=>$row->pembelianDetail()->avg(DB::raw('harga')),
                'stock_masuk'=>$row->pembelianDetail()->avg(DB::raw('qty')),
                'satuan'=>$row->satuan,
                'total_pokok'=>$row->pembelianDetail()->avg(DB::raw('harga*qty')),
            ];
        }));
    }
    public function getBarangMasukold()
    {
        $cari = \request('cari');
        $limit = \request('limit');
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $data = Barang::query();
        $data->selectRaw("*");
        $data->selectRaw("(select avg(harga) from (pembelian_details a inner join pembelians b on a.pembelian_id=b.id and b.status='finish') where barangs.id=a.barang_id and date(b.created_at) between '{$tgl_awal}' and '{$tgl_akhir}') as harga_terakhir");
        $data->selectRaw("(select sum(qty) from (pembelian_details a inner join pembelians b on a.pembelian_id=b.id and b.status='finish') where barangs.id=a.barang_id and date(b.created_at) between '{$tgl_awal}' and '{$tgl_akhir}') as stock_masuk");
        $data->selectRaw("(select sum(harga*qty) from (pembelian_details a inner join pembelians b on a.pembelian_id=b.id and b.status='finish') where barangs.id=a.barang_id and date(b.created_at) between '{$tgl_awal}' and '{$tgl_akhir}') as total_pokok");
        if ($tgl_awal && $tgl_akhir) $data->whereHas('pembelianDetail', function ($q) use ($tgl_awal, $tgl_akhir) {
            $q->whereDate('created_at', '>=', $tgl_awal);
            $q->whereDate('created_at', '<=', $tgl_akhir);
            $q->whereHas('pembelian', function ($q) {
                $q->where('status', 'finish');
            });
        });
        if ($limit) {
            return Boedysoft::getJson(200, 'Data Kategori Barang', $data->skip(0)->take($limit)->get());
        } else {
            return Boedysoft::getJson(200, 'Data Kategori Barang', $data->with('pembelianDetail')->get());
        }
    }

    public function getPembelianBarangDetail($id)
    {
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $data = PembelianDetail::query();
        $data->where('barang_id', $id);
        $data->whereHas('pembelian', function ($q) use ($tgl_awal, $tgl_akhir) {
            $q->whereDate('created_at', '>=', $tgl_awal);
            $q->whereDate('created_at', '<=', $tgl_akhir);
            $q->where('status', 'finish');
        });
        return Boedysoft::getJson(200, 'Data Kategori Barang', $data->get());
    }

    public function setExpaidDate(PembelianDetail $pembelianDetail)
    {
        $expaid = \request('expaid');
        PembelianDetail::find($pembelianDetail->id)->update([
            'expaid' => $expaid
        ]);
        return Boedysoft::getJson(200, 'Sukses mengubah expaid date..!', $pembelianDetail);
    }

    public function getExpaidInformasi()
    {
        $tglAwal = \request('tgl_awal');
        $tglAkhir = \request('tgl_akhir');
        $data = PembelianDetail::query();
        $data->whereDate('expaid', '>=', $tglAwal);
        $data->whereDate('expaid', '<=', $tglAkhir);
        return Boedysoft::getJson(200, null, collect($data->get())->map(function ($it) {
            return [
                'id' => $it->id,
                'nama_barang' => $it->barang->nama_barang,
                'expaid' => Carbon::parse($it->expaid)->format('d M Y'),
                'masa' => Carbon::parse($it->expaid)->diffForHumans(),
                'satuan' => $it->satuan,
                'supplier' => $it->pembelian->supplier->nama,
                'qty' => $it->qty,
                'kategori' => $it->barang->barangKategori->kategori,
                'total' => $it->sub_total
            ];
        }));
    }

    public function hutangHistorySetoran()
    {
        $data = HutangSetoran::query();
        $data->whereHas('perusahaan',function ($q){
            $q->where('owner_id',\request()->user()->perusahaan->owner->id);
        });

        return Boedysoft::getJson(200, null, collect($data->get())->map(function ($row) {
            return [
                'id' => $row->id,
                'unit_setor' => $row->perusahaan->nama,
                'tgl_setoran' => $row->tgl_setoran,
                'nama' => $row->pembelian->supplier->nama,
                'alamat' => $row->pembelian->supplier->alamat,
                'telpon' => $row->pembelian->supplier->telpon,
                'jumlah' => $row->jumlah,
                'penerima' => $row->penerima,
                'catatan' => $row->catatan,
            ];
        }));
    }

    public function getSetoran(HutangSetoran $hutangSetoran)
    {
        return Boedysoft::getJson(200, null, $hutangSetoran);
    }
}
