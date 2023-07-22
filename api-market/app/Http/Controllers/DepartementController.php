<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Departement;
use http\Header\Parser;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    public function index()
    {
        $data = Departement::query();
        $data->where('owner_id',\request()->user()->perusahaan->owner->id);
        return Boedysoft::getJson(200, null, $data->get());
    }

    public function store(Request $request)
    {
        $data = Departement::create(array_merge($request->all(), [
            'owner_id' => $request->user()->perusahaan->owner_id
        ]));
        return Boedysoft::getJson(200, 'Sukess Menambahkan Departement');
    }

    public function show(Departement $departement)
    {
        return Boedysoft::getJson(200, null, $departement);
    }

    public function destroy(Departement $departement)
    {
        $departement->delete();
        return Boedysoft::getJson(200, 'Sukses menghapus data..!');
    }

    public function update(Departement $departement, Request $request)
    {
        $data = $departement->update(array_merge($request->all(), [
            'owner_id' => $request->user()->perusahaan->owner_id
        ]));
        return Boedysoft::getJson(200, 'Sukses Mengubah departement');
    }
}
