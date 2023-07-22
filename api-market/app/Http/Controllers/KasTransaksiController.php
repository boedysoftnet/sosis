<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\AkunPerkiraan;
use App\Models\Bank;
use App\Models\KasTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasTransaksiController extends Controller
{
    public function index()
    {
        $cari = \request('cari');
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $data = KasTransaksi::query();
        $data->whereHas('perusahaan',function ($q){
            $q->where('owner_id',\request()->user()->perusahaan->owner->id);
        });
        if ($cari) {
            $data->where('catatan', 'like', "%$cari%");
            $data->orWhere('kategori', 'like', "%$cari%");
        }
        return Boedysoft::getJson(200, null, $data->orderByDesc('id')->get());
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $bank = Bank::find($request->bank_id);
            $akunPerkiraan = AkunPerkiraan::find($request->akun_perkiraan_id);
            $kelompok = $akunPerkiraan->akunKelompok;
            $golongan = $kelompok->akunGolongan;
            $normalSaldo = $golongan->normal_saldo === 'debet';
            $data = KasTransaksi::create(array_merge($request->all(), [
                'user_id' => $request->user()->id,
                'perusahaan_id' => $request->user()->perusahaan->id
            ]));
            Boedysoft::setHistoryUser($data, "Entry transaksi kas {$request->catatan}");
            if ($normalSaldo) {
                Boedysoft::setJurnal($data, "Pengeluaran Lainnya", $akunPerkiraan->id, $request->nominal, 0, $request->catatan,$request->created_at);
                Boedysoft::setJurnal($data, "Pengeluaran Lainnya", $bank->akun_perkiraan_id, 0, $request->nominal, $request->catatan,$request->created_at);
            } else {
                Boedysoft::setJurnal($data, "Pendapatan Lainnya", $bank->akun_perkiraan_id, $request->nominal, 0, $request->catatan,$request->created_at);
                Boedysoft::setJurnal($data, "Pendapatan Lainnya", $akunPerkiraan->id, 0, $request->nominal, $request->catatan,$request->created_at);
            }
            return Boedysoft::getJson(200, 'Sukses membuat transaksi kas', $data);
        });
    }

    public function destroy($id)
    {
        $kasTransaksi=KasTransaksi::find($id);
        return DB::transaction(function () use ($kasTransaksi) {
            Boedysoft::setHistoryUser($kasTransaksi,\request()->user()->name." Menghapus transaksi kas {$kasTransaksi->catatan} senilai : Rp.{$kasTransaksi->nominal}");
            $kasTransaksi->jurnal()->delete();
            $kasTransaksi->forceDelete();
            return Boedysoft::getJson(200,'Sukses menghapus transaksi kas..!');
        });
    }
}
