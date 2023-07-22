<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\Point;
use Cassandra\Custom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index()
    {
        $cari = \request('cari');
        $limit = \request('limit');
        $data = Customer::query();
        if ($cari) $data->where(function ($q) use ($cari) {
            $q->where('nama', 'like', "%$cari%");
            $q->orWhere('alamat', 'like', "%$cari%");
            $q->orWhere('telpon', 'like', "%$cari%");
            $q->orWhere('email', 'like', "%$cari%");
        });
        $data->whereHas('perusahaan', function ($q) {
            $q->where('owner_id', \request()->user()->perusahaan->owner->id);
        });
        $data->orderByDesc('id');
        if ($limit) {
            return Boedysoft::getJson(200, '', $data->skip(0)->take($limit)->get());
        } else {
            return Boedysoft::getJson(200, '', $data->get());
        }
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'nama' => 'required|min:5',
            'alamat' => 'required',
            'email' => 'required|email',
            'telpon' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf form tidak falid..!', $valid->errors());
        } else {
            $form = $request->all();
            if ($request->photo) if (gettype($request->photo) != 'string') $form['photo'] = Boedysoft::copyStorage('customer', $request->photo);
            if ($request->id) {
                $data = Customer::find($request->id);
                $data->update(array_merge($form, [
                    'user_id' => $request->user()->id,
                    'perusahaan_id' => $request->user()->perusahaan_id,
                ]));
                return Boedysoft::getJson(200, 'Sukses Edit data customer..!', $data);
            } else {
                $data = Customer::create(array_merge($form, [
                    'user_id' => $request->user()->id,
                    'perusahaan_id' => $request->user()->perusahaan_id,
                ]));
                return Boedysoft::getJson(200, 'Sukses membuat data customer..!', $data);
            }
        }
    }

    public function show(Customer $customer)
    {
        return Boedysoft::getJson(200, null, $customer);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return Boedysoft::getJson(200, 'Sukses menghapus data..!');
    }

    public function getPointPerolehan()
    {
        $data = Customer::query();
        $data->where('id','<>',1);
        return Boedysoft::getJson(200, null, $data->get()->map(function ($row) {
            $point = DB::select("select sum(point) as point,sum(tukar) as tukar,sum(point-tukar) as sisa from (penjualans a inner join points b on a.id=b.penjualan_id) where customer_id='" . $row->id . "'");
            return [
                'id' => $row->id,
                'nama_customer' => $row->nama,
                'alamat' => $row->alamat,
                'telpon' => $row->telpon,
                'penukaran' => collect($point)->first()->tukar??0,
                'perolehan' => collect($point)->first()->point??0,
                'akumulasi_point' => collect($point)->first()->sisa??0,
                'items' => DB::select("select a.id,a.created_at,jenis,total,nilai_kelipatan,round(point-0.49) as point,tukar,point-tukar as sisa from (penjualans a inner join points b on a.id=b.penjualan_id) where a.customer_id=" . $row->id)
            ];
        }));
    }

    public function simpanTukarPoint(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        $jumlahTukar = $request->tukar;
        foreach ($customer->penjualan as $items) {
            foreach ($items->point as $item) {
                $sisa = $item->point - $item->tukar;
                if ($sisa > 0) {
                    if ($sisa < $jumlahTukar) {
                        $item->tukar += $sisa;
                        $item->save();
                        $jumlahTukar -= $sisa;
                    } else {
                        $item->tukar += $jumlahTukar;
                        $item->save();
                        $jumlahTukar = 0;
                    }
                    if ($jumlahTukar <= 0) return Boedysoft::getJson(200, 'selesai', $jumlahTukar);
                }
            }
        }
        return Boedysoft::getJson(200, 'Sukses menukarkan point..!');
    }
}
