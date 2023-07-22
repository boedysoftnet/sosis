<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class UserController extends Controller
{
    public function validUser()
    {
        return Boedysoft::getJson(201, 'Token Id Tidak falid..!', 'Jangan coba-coba yach');
    }

    public function logout(){
        Auth::logout();
        return Boedysoft::getJson(200,"Sukses logout..!");
    }


    public function authUser(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Format Tidak falid..!', $valid->getMessageBag());
        } else {
            $data = User::whereEmail($request->email)->wherePassword($request->password);
            if ($data->count()) {
                $data->first()->userHistory()->create([
                    'user_id' => $data->first()->id,
                    'body' => "{$data->first()->name} Berhasil masuk system...!"
                ]);
                return Boedysoft::getJson(200, 'User Berhasil Login...!', [
                    'users' => $data->first(),
                    'token_id' => $data->first()->createToken('admin')->plainTextToken
                ]);
            } else {
//                Boedysoft::setHistoryUser($data,"Ada yang mencoba untuk masuk system..!");
                return Boedysoft::getJson(201, 'Maaf Email dan password tidak falid..!');
            }
        }
    }

    public function profile()
    {
        if(\request('is_uses')){
            return Boedysoft::getJson(200, 'Informasi', [
                'users'=>\request()->user()
            ]);
        }else{
            return Boedysoft::getJson(200, 'Informasi', \request()->user());
        }
    }

    public function index()
    {
        $data = User::query();
        return Boedysoft::getJson(200, null, $data->get());
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Format Tidak falid..!', $valid->getMessageBag());
        } else {
            $data = $request->all();
            if (gettype($request->photo) != 'string') $data['photo'] = Boedysoft::copyStorage('user', $request->photo);
            $data = User::create($data);
            if ($data) {
                return Boedysoft::getJson(200, 'User berhasil ditambahkan', $data);
            } else {
                return Boedysoft::getJson(200, 'Maaf Email dan password tidak falid..!');
            }
        }
    }

    public function show(User $user)
    {
        if ($user) {
            return Boedysoft::getJson(200, null, $user);
        } else {
            return Boedysoft::getJson(404, 'Tidak ditemukan');
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        if (gettype($request->photo) != 'string' && $request->photo!=null) $data['photo'] = Boedysoft::copyStorage('user', $request->photo);
        User::find($id)->update($data);
        return Boedysoft::getJson(200,'Sukses mengubah datas..!',$request->all());
    }

    public function destroy($id)
    {
        User::find($id)->delete();
        return Boedysoft::getJson(200,'Sukses menghapus data..');
    }

}
