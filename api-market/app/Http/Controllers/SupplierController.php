<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index()
    {
        $data = Supplier::query();
        $data->without('user');
        return Boedysoft::getJson(200, 'Data Supplier', $data->get());
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'nama' => 'required',
            'alamat' => 'required',
            'telpon' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            $data = Supplier::create(array_merge($request->all(), ['user_id' => $request->user()->id]));
            return Boedysoft::getJson(200, 'Data Supplier berhasil dibuat..!', $data);
        }
    }

    public function update(Request $request, $id)
    {
        $valid = Validator::make($request->all(), [
            'nama' => 'required',
            'alamat' => 'required',
            'telpon' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            $data = Supplier::find($id)->update(array_merge($request->all(), ['user_id' => $request->user()->id]));
            return Boedysoft::getJson(200, 'Data Supplier berhasil edit..!', $data);
        }
    }

    public function show(Supplier $supplier)
    {
        return Boedysoft::getJson(200, 'Informasi', $supplier);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return Boedysoft::getJson(200, 'Supplier berhasil dihapus..!');
    }
}
