<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankController extends Controller
{
    public function index()
    {
        $data = Bank::query();
        $data->without('user');
        return Boedysoft::getJson(200, 'Data Bank', $data->orderBy('id')->get());
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'nama_bank' => 'required',
            'alamat' => 'required',
            'telpon' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            return DB::transaction(function () use ($request) {
                $data = Bank::create(array_merge($request->all(), ['user_id' => $request->user()->id]));
                return Boedysoft::getJson(200, 'Data Bank berhasil dibuat..!', $data);
            });
        }
    }

    public function update(Request $request, $id)
    {
        $valid = Validator::make($request->all(), [
            'nama_bank' => 'required',
            'alamat' => 'required',
            'telpon' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            return DB::transaction(function () use($request,$id){
                $data = Bank::find($id);
                $data->update(array_merge($request->all(), ['user_id' => $request->user()->id]));
                return Boedysoft::getJson(200, 'Data Bank berhasil edit..!', $data);
            });
        }
    }

    public function show(Bank $bank)
    {
        return Boedysoft::getJson(200, 'Informasi', $bank);
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return Boedysoft::getJson(200, 'Bank berhasil dihapus..!');
    }
}
