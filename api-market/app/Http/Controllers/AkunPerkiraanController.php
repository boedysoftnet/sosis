<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\AkunKelompok;
use App\Models\AkunPerkiraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AkunPerkiraanController extends Controller
{
    public function index()
    {
        $cari = \request('cari');
        $periode = \request('periode');
        $limit = \request('limit');
        $data = AkunPerkiraan::query();
        if ($periode) $data->whereHas('jurnal', function ($q) use ($periode) {
            $q->whereDate('created_at', '<=', $periode);
            $q->where('perusahaan_id', \request()->user()->perusahaan_id);
        });
        if ($cari) $data->where('nama_akun', 'like', "%$cari%");
        if ($limit) {
            return Boedysoft::getJson(200, 'Data Akun Perkiraan', $data->orderBy('kode_akun')->skip(0)->take($limit)->get());
        } else {
            return Boedysoft::getJson(200, 'Data Akun Perkiraan', $data->orderBy('kode_akun')->get());
        }
    }

    public function destroy(AkunPerkiraan $akunPerkiraan)
    {
        return DB::transaction(function () use ($akunPerkiraan) {
            $akunPerkiraan->delete();
        });
    }

    public function getRugiLaba()
    {
        $tglAwal = \request('tgl_awal');
        $tglAkhir = \request('tgl_akhir');
        $data = AkunPerkiraan::query();
        $data->whereHas('akunKelompok', function ($q) {
            $q->whereHas('akunGolongan', function ($q) {
                $q->where('is_neraca', 0);
            });
        });
        $data->whereHas('jurnal', function ($q) use ($tglAwal, $tglAkhir) {
            $q->whereDate('created_at', '>=', $tglAwal);
            $q->whereDate('created_at', '<=', $tglAkhir);
            $q->whereHas('perusahaan',function ($q){
                $q->where('owner_id',\request()->user()->perusahaan->owner->id);
            });
        });
        return Boedysoft::getJson(200, null, $data->get());
    }

    public function showTransaksi()
    {
        $data = AkunPerkiraan::query();
        $data->where('show_transaksi',1);
        return Boedysoft::getJson(200, null, $data->get());
    }
}
