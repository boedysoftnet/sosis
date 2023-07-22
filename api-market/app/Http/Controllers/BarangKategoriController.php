<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\BarangKategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarangKategoriController extends Controller
{
    public function index()
    {
        $data = BarangKategori::query();
        return Boedysoft::getJson(200, 'Data Kategori Barang', $data->get());
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'kategori' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            $data = BarangKategori::create(array_merge($request->all(), ['user_id' => $request->user()->id]));
            return Boedysoft::getJson(200, 'Data Kategori Barang berhasil dibuat..!', $data);
        }
    }

    public function update(Request $request,$id)
    {
        $valid = Validator::make($request->all(), [
            'kategori' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            $data = BarangKategori::find($id)->update(array_merge($request->all(), ['user_id' => $request->user()->id]));
            return Boedysoft::getJson(200, 'Data Kategori Barang berhasil edit..!', $data);
        }
    }

    public function show(BarangKategori $barangKategori)
    {
        return Boedysoft::getJson(200, 'Informasi', $barangKategori);
    }

    public function destroy(BarangKategori $barangKategori)
    {
        $barangKategori->delete();
        return Boedysoft::getJson(200, 'Barang Kategori berhasil dihapus..!');
    }

}
