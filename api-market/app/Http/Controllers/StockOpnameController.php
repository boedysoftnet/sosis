<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Barang;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index()
    {
        $tglAwal=\request('tgl_awal');
        $tglAkhir=\request('tgl_akhir');
        $data = StockOpname::query();
        $data->whereHas('perusahaan',function ($q){
            $q->where('owner_id',\request()->user()->perusahaan->owner->id);
        });
        $data->whereDate('created_at','>=',$tglAwal);
        $data->whereDate('created_at','<=',$tglAkhir);
        $data->orderByDesc('id');
        return Boedysoft::getJson(200, '', collect($data->get())->map(function ($it) {
            return [
                'id' => $it->id,
                'barang_id' => $it->barang->id,
                'nama_barang' => $it->barang->nama_barang,
                'harga' => $it->harga,
                'qty' => $it->qty,
                'stock_sebelumnya' => $it->stock_sebelumnya,
                'stock_akhir' => $it->stock_sebelumnya+$it->qty,
                'satuan' => $it->satuan,
                'total' => $it->total,
                'alasan' => $it->alasan ?? '-',
            ];
        }));
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $sisa = $request->qty - Boedysoft::getStock($request->id);
            $barang=Barang::find($request->id);
            $data = StockOpname::create(array_merge($request->all(), [
                'perusahaan_id' => $request->user()->perusahaan_id,
                'user_id' => $request->user()->id,
                'barang_id' => $barang->id,
                'harga' => $barang->pokok,
                'qty' => $sisa,
                'alasan' => $request->alasan,
                'stock_sebelumnya' => Boedysoft::getStock($request->id),
            ]));
            if ($sisa > 0) {
                Boedysoft::setStockHistory($data, 'Opname Stock', $request->id, $request->qty, "Penambahan stock via opname sejumlah {$data->qty} {$data->satuan} dengan nominal : {$data->total}");
                Boedysoft::setStock($data->barang_id, $sisa);
                Boedysoft::setHistoryUser($data,"Menambahkan stock via opname pada {$data->barang->nama_barang} sejumlah {$data->qty} dengan total {$data->total}");
            } else {
                Boedysoft::setStockHistory($data, 'Opname Stock', $request->id, $request->qty, "Pengurangan stock via opname sejumlah {$data->qty} {$data->satuan} dengan nominal : {$data->total}");
                Boedysoft::setStock($data->barang_id, $sisa);
                Boedysoft::setHistoryUser($data,"Menguragi stock via opname pada {$data->barang->nama_barang} sejumlah {$data->qty} dengan total {$data->total}");
            }
            $this->catatanPembukuan($data);
            return Boedysoft::getJson(200, 'Stock Opname berhasil...');
        });
    }

    public function catatanPembukuan($data){
        if($data->qty>0){
            $catatan="Menambahkan persediaan {$data->barang->nama_barang} via opname stock sejumlah {$data->qty} dengan total : {$data->total}";
            Boedysoft::setJurnalWithParameter($data,'Opname Stock','persediaan barang',$data->total,0,$catatan);
            Boedysoft::setJurnalWithParameter($data,'Opname Stock','modal owner',0,$data->total,$catatan);
        }else{
            $catatan="Mengurangi persediaan {$data->barang->nama_barang} via opname stock sejumlah {$data->qty} dengan total : {$data->total}";
            Boedysoft::setJurnalWithParameter($data,'Opname Stock','stock opname',abs($data->total),0,$catatan);
            Boedysoft::setJurnalWithParameter($data,'Opname Stock','persediaan barang',0,abs($data->total),$catatan);
        }
    }
}
