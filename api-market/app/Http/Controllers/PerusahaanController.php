<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerusahaanController extends Controller
{
    public function index()
    {
        $data = Perusahaan::query();
        $data->without('user');
        $data->where('owner_id',\request()->user()->perusahaan->owner_id);
        return Boedysoft::getJson(200, 'Data Perusahaan', $data->get());
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
            $form = $request->all();
            $form['owner_id']=$request->user()->perusahaan->owner_id;
            if (gettype($request->icon) != 'string') $form['icon'] = Boedysoft::copyStorage('perusahaan', $request->icon,120);
            $data = Perusahaan::create($form);
            return Boedysoft::getJson(200, 'Data Perusahaan berhasil dibuat..!', $data);
        }
    }

    public function update(Request $request, $id)
    {
        $form = $request->all();
        $valid = Validator::make($form, [
            'nama' => 'required',
            'alamat' => 'required',
            'telpon' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            if (gettype($request->icon) != 'string') $form['icon'] = Boedysoft::copyStorage('perusahaan', $request->icon);
            $data = Perusahaan::find($id)->update(array_merge($form, [
                'user_id' => $request->user()->id,
                'owner_id'=>$request->user()->perusahaan->owner_id
            ]));
            return Boedysoft::getJson(200, 'Data Perusahaan berhasil edit..!', $data);
        }
    }

    public function show(Perusahaan $perusahaan)
    {
        return Boedysoft::getJson(200, 'Informasi', $perusahaan);
    }

    public function destroy(Perusahaan $perusahaan)
    {
        $perusahaan->delete();
        return Boedysoft::getJson(200, 'Perusahaan berhasil dihapus..!');
    }
}
