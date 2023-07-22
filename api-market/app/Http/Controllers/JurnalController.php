<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\AkunPerkiraan;
use App\Models\Barang;
use App\Models\Jurnal;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalController extends Controller
{
    public function resetDatabase()
    {
        DB::statement("SET foreign_key_checks=0");
        Penjualan::query()->forceDelete();
        Pembelian::query()->forceDelete();
        Produksi::query()->forceDelete();
        Stock::query()->forceDelete();
        ProduksiDetail::query()->forceDelete();
        Jurnal::query()->forceDelete();
        foreach (Barang::all() as $item) {
            $item->harga_net = 0;
            $item->save();
        }
        DB::statement("SET foreign_key_checks=1");
        return Boedysoft::getJson(200, 'Sukses Mereset database..!');
    }

    public $total = 0;

    public function showArusPembukuan(AkunPerkiraan $akunPerkiraan)
    {
        $periode = \request('tgl_akhir');
        return Boedysoft::getJson(200, null, collect($akunPerkiraan->jurnal()->whereDate('created_at', '<=', $periode)->get())->map(function ($row) {
            $akunPerkiraan = AkunPerkiraan::find($row->akun_perkiraan_id);
            $this->total += ($row->debet - $row->kredit);
            return [
                "id" => $row->id,
                "catatan" => $row->catatan,
                "kategori" => $row->kategori,
                "nama_akun" => $akunPerkiraan->nama_akun,
                "debet" => $row->debet,
                "kredit" => $row->kredit,
                "created_at" => $row->created_at,
                "total" => $this->total,
                "user" => User::find($row->user_id)->name
            ];
        }));
    }

    public function showJurnalPrint(AkunPerkiraan $akunPerkiraan)
    {
        return Boedysoft::getJson(200, null, $akunPerkiraan);
    }
}
