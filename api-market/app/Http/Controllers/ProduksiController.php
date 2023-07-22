<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\Jurnal;
use App\Models\Produksi;
use App\Models\ProduksiDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function MongoDB\BSON\toRelaxedExtendedJSON;

class ProduksiController extends Controller
{
    public function index()
    {
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $filter_type = \request('filter_type');
        $data = Produksi::query();
        $data->where('perusahaan_id', \request()->user()->perusahaan_id);
        $data->without('user');
        $data->whereNotNull('valid');
        if ($filter_type == 'selesai') $data->whereNotNull('tgl_selesai');
        if ($filter_type == 'masih produksi') $data->whereNull('tgl_selesai');
        if ($tgl_awal && $tgl_akhir && $filter_type != 'masih produksi') {
            $data->whereDate('created_at', '>=', $tgl_awal);
            $data->whereDate('created_at', '<=', $tgl_akhir);
        }
        return Boedysoft::getJson(200, null, collect($data->orderBy('id')->get())->map(function ($row) {
            return [
                'id' => $row->id,
                'perusahaan' => $row->perusahaan->nama,
                'barang_produksi' => $row->barang->nama_barang,
                'tgl_produksi' => $row->tgl_produksi,
                'tgl_selesai' => $row->tgl_selesai,
                'created_at' => $row->created_at,
                'total' => $row->total,
                'ppn' => $row->ppn,
                'hasil_produksi' => $row->hasil_produksi,
                'estimasi' => $row->estimasi,
                'status' => $row->tgl_selesai ? 'Selesai' : 'Proses Produksi',
                'user' => $row->user->name,
                'proses' => Carbon::parse($row->tgl_selesai)->diffForHumans(Carbon::parse($row->tgl_produksi)),
            ];
        }));
    }

    public function show(Produksi $produksi)
    {
        return Boedysoft::getJson(200, null, $produksi);
    }

    public function buatProduksi(Request $request, $id = null)
    {
        if ($id) {
            $data = Produksi::find($id);
            return Boedysoft::getJson(200, null, $data);
        } else {
            $data = Produksi::whereNull('valid')->where('user_id', $request->user()->id);
            if ($data->count() > 0) {
                return Boedysoft::getJson(200, null, $data->first());
            } else {
                $data = Produksi::create([
                    'perusahaan_id' => $request->user()->perusahaan_id,
                    'user_id' => $request->user()->id,
                    'estimasi' => 1,
                    'barang_id' => 1
                ]);
                return Boedysoft::getJson(200, null, Produksi::find($data->id));
            }
        }
    }

    public function addBahanBaku(Produksi $produksi, Request $request)
    {
        return DB::transaction(function () use ($produksi, $request) {
            $ppn = Boedysoft::getKonfigurasiSystem('pungutan ppn');
            if(Boedysoft::getKonfigurasiSystem('produksi non ppn')) $ppn=0;
            $barang = Barang::find($request->id);
            $satuan = $barang->barangSatuan()->where('satuan', $request->satuan)->first();
            $harga = $barang->type_jasa ? $request->pokok : $barang->harga_net;
            $produksi->produksiDetail()->create([
                'barang_id' => $request->id,
                'jumlah' => $request->jumlah,
                'satuan' => $satuan->satuan,
                'satuan_isi' => $satuan->isi,
                'harga' => $harga,
                'ppn' => ($harga*$request->jumlah) * $ppn
            ]);
            return Boedysoft::getJson(200, 'Sukses Menambahkan item produk..!');
        });
    }

    public function destroyDetail($id)
    {
        ProduksiDetail::find($id)->delete();
        return Boedysoft::getJson(200, null, $id);
    }

    public function simpanProduksi(Produksi $produksi, Request $request)
    {
        return DB::transaction(function () use ($produksi, $request) {
            if ($request->finish) {
                $produksi->update([
                    'valid' => Carbon::now(),
                    'estimasi' => $request->estimasi,
                    'tgl_produksi' => Carbon::now(),
                    'barang_id' => $request->barang_id
                ]);
            } else {
                $produksi->update([
                    'barang_id' => $request->barang_id
                ]);
            }
            return Boedysoft::getJson(200, 'Produksi Berhasil disimpan..!');
        });
    }

    public function setJumlahBahan($id, Request $request)
    {
        $stock = Boedysoft::getStock($request->barang_id);
        $barang=Barang::find($request->barang_id);
        if($barang->type_jasa==1){
            $satuan = BarangSatuan::where('barang_id', $request->barang_id)->where('satuan', $request->satuan);
            $ppn = Boedysoft::getKonfigurasiSystem('pungutan ppn');
            if(Boedysoft::getKonfigurasiSystem('produksi non ppn')) $ppn=0;
            if ($satuan->count() > 0) {
                $data = ProduksiDetail::find($id);
                $data->update([
                    'jumlah' => $request->qty,
                    'satuan' => $satuan->first()->satuan,
                    'satuan_isi' => $satuan->first()->isi,
                    'ppn' => ($data->harga * $request->qty)*$ppn
                ]);
                return Boedysoft::getJson(200, 'Jumlah bahan baku berhasil di sesuaikan..!');
            }
        }else{
            if ($stock >= $request->qty) {
                $satuan = BarangSatuan::where('barang_id', $request->barang_id)->where('satuan', $request->satuan);
                $ppn = Boedysoft::getKonfigurasiSystem('pungutan ppn');
                if(Boedysoft::getKonfigurasiSystem('produksi non ppn')) $ppn=0;
                if ($satuan->count() > 0) {
                    $data = ProduksiDetail::find($id);
                    $data->update([
                        'jumlah' => $request->qty,
                        'satuan' => $satuan->first()->satuan,
                        'satuan_isi' => $satuan->first()->isi,
                        'ppn' => ($data->harga * $request->qty)*$ppn
                    ]);
                    return Boedysoft::getJson(200, 'Jumlah bahan baku berhasil di sesuaikan..!');
                }
            } else {
                return Boedysoft::getJson(201, 'Maaf Jumlah yang anda input lebih besar dari stock tersedia..!');
            }
        }
    }

    public function konfirmasiProduksi(Produksi $produksi, Request $request)
    {
        return DB::transaction(function () use ($produksi, $request) {
            $this->kurangiBahanBaku($produksi);
            $produksi->update([
                'tgl_selesai' => Carbon::now(),
                'hasil_produksi' => $request->hasil_produksi,
            ]);
            $this->tambahkanStockProduksi($produksi);
            $this->prosesJurnalProduksi($produksi);
            $this->prosesJurnalPersediaan($produksi);
            return Boedysoft::getJson(200, 'Hasil produksi telah di konfirmasi..!', $produksi);
        });
    }

    public function prosesJurnalProduksi(Produksi $produksi)
    {
        $nil_persediaan = $produksi->produksiDetail()->sum(DB::raw('(jumlah*satuan_isi)*harga'));
        $catatan = "Proses Produksi {$produksi->barang->nama_barang} senilai pokok : Rp. {$nil_persediaan}";
        $ppn = Boedysoft::getKonfigurasiSystem('pungutan ppn');
        $beban_ppn=0;
        if(!Boedysoft::getKonfigurasiSystem('produksi non ppn')) $beban_ppn = $nil_persediaan * $ppn;
        Boedysoft::setJurnalWithParameter($produksi, "Penyesuaian Produksi", "Produksi Bahan Baku", $nil_persediaan + $beban_ppn, 0, $catatan);
        if(!Boedysoft::getKonfigurasiSystem('produksi non ppn')) Boedysoft::setJurnalWithParameter($produksi, "Penyesuaian Produksi", "PPN Keluaran", 0, $beban_ppn, $catatan);
        Boedysoft::setJurnalWithParameter($produksi, "Penyesuaian Produksi", "Persediaan Barang", 0, $nil_persediaan, $catatan);
    }

    public function prosesJurnalPersediaan(Produksi $produksi)
    {
        $nil_persediaan = $produksi->produksiDetail()->sum(DB::raw('(jumlah*satuan_isi)*harga'));
        $catatan = "Proses menambahkan produksi {$produksi->barang->nama_barang} senilai pokok : Rp. {$nil_persediaan}";
        $ppn = Boedysoft::getKonfigurasiSystem('pungutan ppn');
        $beban_ppn=0;
        if(!Boedysoft::getKonfigurasiSystem('produksi non ppn')) $beban_ppn = $nil_persediaan * $ppn;
        Boedysoft::setJurnalWithParameter($produksi, "Hasil Produksi", "Persediaan Barang", $nil_persediaan, 0, $catatan);
        if(!Boedysoft::getKonfigurasiSystem('produksi non ppn')) Boedysoft::setJurnalWithParameter($produksi, "Hasil Produksi", "PPN Masukan", $beban_ppn, 0, $catatan);
        Boedysoft::setJurnalWithParameter($produksi, "Hasil Produksi", "Produksi Bahan Baku", 0, ($nil_persediaan + $beban_ppn), $catatan);
    }

    public function kurangiBahanBaku(Produksi $produksi)
    {
        $user = \request()->user();
        foreach ($produksi->produksiDetail as $item) {
            $catatan = "{$user->name} memproses produksi bahan baku {$item->barang->nama_barang}";
            Boedysoft::setStockHistory($item, "Konfirmasi Hasil Produksi", $item->barang_id, -$item->jumlah, $catatan);
            if (!$item->barang->type_jasa) Boedysoft::setStock($item->barang_id, -$item->jumlah);
        }
    }

    public function tambahkanStockProduksi(Produksi $produksi)
    {
        $user = \request()->user();
        $catatan = "{$user->name} konfirmasi hasil produksi akhir {$produksi->barang->nama_barang} senilai {$produksi->hasil_produksi}";
        $hpp = $produksi->total_setelah_ppn / $produksi->hasil_produksi;
        Boedysoft::setHargaNet($produksi->barang_id, $produksi->hasil_produksi, $hpp);
        Boedysoft::setStockHistory($produksi, "Konfirmasi Hasil Produksi", $produksi->barang_id, $produksi->hasil_produksi, $catatan);
        Boedysoft::setStock($produksi->barang_id, $produksi->hasil_produksi);
    }

    public function destroy(Produksi $produksi)
    {
        $produksi->delete();
        return Boedysoft::getJson(200, "Sukses menghapus data produksi..!");
    }

    public function hapusProduksi(Produksi  $produksi){
//        $this->kurangiBahanBaku($produksi);
//        $produksi->update([
//            'tgl_selesai' => Carbon::now(),
//            'hasil_produksi' => $request->hasil_produksi,
//        ]);
//        $this->tambahkanStockProduksi($produksi);
//        $this->prosesJurnalProduksi($produksi);
//        $this->prosesJurnalPersediaan($produksi);
        return DB::transaction(function () use($produksi){
            //kembalikan bahan baku
            foreach ($produksi->produksiDetail as $item){
                Boedysoft::setStock($item->barang_id,$item->jumlah);
            }
            Boedysoft::setStock($produksi->barang_id,-$produksi->hasil_produksi);
            $hpp = $produksi->total_setelah_ppn / $produksi->hasil_produksi;
            Boedysoft::setHargaNet($produksi->barang_id, -$produksi->hasil_produksi, $hpp);
            $produksi->jurnal()->forceDelete();
            $produksi->stockHistory()->forceDelete();
            $produksi->delete();
            return Boedysoft::getJson(200,'Sukses menghapus data..!');
        });
    }
}
