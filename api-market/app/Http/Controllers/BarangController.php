<?php

namespace App\Http\Controllers;

use App\Helpers\Boedysoft;
use App\Imports\BarangImport;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    public function index()
    {
        $cari = \request('cari');
        $limit = \request('limit');
        $produk_only = \request('produk_only');
        $barang_kategori_id=\request('barang_kategori_id');
        $tgl_awal = \request('tgl_awal');
        $tgl_akhir = \request('tgl_akhir');
        $data = Barang::query();
        if ($cari) $data->where('nama_barang', 'like', "%$cari%");
        $data->without('user', 'stock');
        if ($produk_only) $data->where('type_jasa', 0);
        if($barang_kategori_id) $data->where('barang_kategori_id',$barang_kategori_id);
        if ($limit) {
            return Boedysoft::getJson(200, 'Data Kategori Barang', $data->orderByDesc('id')->skip(0)->take($limit)->get());
        } else {
            return Boedysoft::getJson(200, 'Data Kategori Barang', $data->orderByDesc('id')->get());
        }
    }

    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'nama_barang' => 'required',
            'barang_kategori_id' => 'required',
            'pokok' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            return DB::transaction(function () use ($request) {
                $data = Barang::create(array_merge($request->all(), [
                    'user_id' => $request->user()->id,
                    'harga_net' => $request->pokok
                ]));
                Boedysoft::setHistoryUser($data, 'Membuat data profile barang baru..!');
                foreach ($request->barang_satuan as $item)
                    $data->barangSatuan()->create([
                        'perusahaan_id' => $request->user()->perusahaan_id,
                        'satuan' => $item['satuan'],
                        'harga' => $item['harga'],
                        'isi' => $item['isi'],
                    ]);
                return Boedysoft::getJson(200, 'Data Kategori Barang berhasil dibuat..!', $data);
            });
        }
    }

    public function importData(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $path = Storage::disk('public')->put('import', $request->excel);
            Excel::import(new BarangImport, public_path('storage/' . $path));
            Boedysoft::setHistoryUser(Auth::user(), 'Import Data Barang Via Excel');
            return Boedysoft::getJson(200,'Sukses Import Data Barang..!');
        });
    }

    public function update(Request $request, $id)
    {
        $valid = Validator::make($request->all(), [
            'nama_barang' => 'required',
            'barang_kategori_id' => 'required',
            'pokok' => 'required',
        ]);
        if ($valid->fails()) {
            return Boedysoft::getJson(201, 'Maaf Data tidak lengkap..!', $valid->getMessageBag());
        } else {
            return DB::transaction(function () use ($request, $id) {
                $data = Barang::find($id);
                $data->update(array_merge($request->all(), ['user_id' => $request->user()->id]));
                $data->barangSatuan()->delete();
                Boedysoft::setHistoryUser($data, 'Memperbaharui data profile barang..!');
                foreach ($request->barang_satuan as $item)
                    $data->barangSatuan()->create([
                        'perusahaan_id' => $request->user()->perusahaan_id,
                        'satuan' => $item['satuan'],
                        'harga' => $item['harga'],
                        'isi' => $item['isi'],
                    ]);
                return Boedysoft::getJson(200, 'Data Kategori Barang berhasil edit..!', $data);
            });
        }
    }

    public function show(Barang $barang)
    {
        $data = Barang::query();
        $data->without('satuan');
        return Boedysoft::getJson(200, 'Informasi', $data->find($barang->id));
    }

    public function showWithSatuan(Barang $barang)
    {
        $data = Barang::query();
        $data->without('satuan');
        return Boedysoft::getJson(200, 'Informasi', [
            'data' => $data->find($barang->id),
            'satuan' => BarangSatuan::whereBarangId($barang->id)->get()
        ]);
    }

    public function destroy(Barang $barang)
    {
        return DB::transaction(function () use ($barang) {
            Boedysoft::setHistoryUser($barang, 'Menghapus data barang..!');
            $barang->delete();
            return Boedysoft::getJson(200, 'Barang Kategori berhasil dihapus..!');
        });
    }

}
