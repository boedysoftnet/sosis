<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\HutangSetoran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HutangSetoranController extends Controller
{
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $data = HutangSetoran::create(array_merge($request->all(), [
                'user_id' => $request->user()->id,
                'perusahaan_id' => $request->user()->perusahaan->id,
                'pembelian_id' => $request->pembelian_id
            ]));
            $this->buatJurnal($data, $request);
            Boedysoft::setHistoryUser($data,"{$request->user()->name} menyetorkan hutang kepada {$data->pembelian->supplier->nama} senilai : {$data->jumlah}");
            return Boedysoft::getJson(200,'Sukses Menyimpan setoran hutang..!');
        });
    }

    public function buatJurnal(HutangSetoran $hutangSetoran, Request $request)
    {
        $catatan = "Pembayaran Hutang kepada : {$hutangSetoran->pembelian->supplier->nama} senilai : {$hutangSetoran->jumlah} dan diterima oleh : {$hutangSetoran->penerima}";
        Boedysoft::setJurnalWithParameter($hutangSetoran, 'Setoran Hutang', 'hutang pada supplier', $request->jumlah, 0, $catatan,$request->tgl_setoran);
        Boedysoft::setJurnal($hutangSetoran, 'Setoran Hutang', $hutangSetoran->bank->akun_perkiraan_id, 0, $request->jumlah, $catatan,$request->tgl_setoran);
    }

}
