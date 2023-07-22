<?php

namespace App\Helpers;

use App\Models\AkunMapping;
use App\Models\Barang;
use App\Models\Konfigruasi;
use App\Models\Perusahaan;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class Boedysoft
{
    protected static $response;

    public static function getJson($code = null, $message = null, $data = [])
    {
        self::$response['code'] = $code;
        self::$response['message'] = $message;
        self::$response['data'] = $data;
        return response()->json(self::$response, self::$response['code']);
    }

    public static function setStock($barang_id, $stock)
    {
        $data = Stock::query();
        $data->where('barang_id', $barang_id);
        $data->where('perusahaan_id', request()->user()->perusahaan_id);
        if ($data->count() > 0) {
            $data->first()->update(['stock' => $data->first()->stock + $stock]);
        } else {
            request()->user()->perusahaan->stock()->create([
                'barang_id' => $barang_id,
                'stock' => $stock,
            ]);
        }
    }

    public static function getStock($barang_id)
    {
        $data = Stock::query();
        $data->where('barang_id', $barang_id);
        $data->where('perusahaan_id', request()->user()->perusahaan_id);
        if ($data->count() > 0) {
            return $data->first()->stock;
        } else {
            return 0;
        }
    }

    public static function setHistoryUser($model, $catatan)
    {
        $model->userHistory()->create([
            'user_id' => request()->user()->id,
            'body' => $catatan
        ]);
    }

    public static function setJurnal($model, $kategori, $akun_perkiraan_id, $debet, $kredit, $catatan, $tgl_post = null)
    {
        if ($tgl_post == null) $tgl_post = Carbon::now();
        $model->jurnal()->create([
            'user_id' => request()->user()->id,
            'perusahaan_id' => request()->user()->perusahaan->id,
            'akun_perkiraan_id' => $akun_perkiraan_id,
            'debet' => $debet,
            'kategori' => $kategori,
            'kredit' => $kredit,
            'catatan' => $catatan,
            'created_at' => $tgl_post,
            'updated_at' => $tgl_post,
        ]);
    }

    public static function setJurnalWithParameter($model, $kategori, $nama_akun, $debet = 0, $kredit = 0, $catatan = null,$tgl_post = null)
    {
        if ($tgl_post == null) $tgl_post = Carbon::now();
        $akunPerkiraan = AkunMapping::query();
        $akunPerkiraan->where('slug', $nama_akun);
        if ($akunPerkiraan->count() > 0) {
            $model->jurnal()->create([
                'user_id' => request()->user()->id,
                'perusahaan_id' => request()->user()->perusahaan->id,
                'akun_perkiraan_id' => $akunPerkiraan->first()->akun_perkiraan_id,
                'debet' => $debet,
                'kategori' => $kategori,
                'kredit' => $kredit,
                'created_at' => $tgl_post,
                'updated_at' => $tgl_post,
                'catatan' => $catatan
            ]);
        } else {
            DB::rollBack();
            return self::getJson(201, "Maaf akun {$nama_akun} belum terdaftar..!");
        }
    }

    public static function setStockHistory($model, $kelompok, $barang_id, $qty, $catatan = null)
    {
        $model->stockHistory()->create([
            'user_id' => request()->user()->id,
            'perusahaan_id' => request()->user()->perusahaan->id,
            'barang_id' => $barang_id,
            'kelompok' => $kelompok,
            'stock_sebelumnya' => self::getStock($barang_id),
            'qty' => $qty,
            'catatan' => $catatan
        ]);
    }

    public static function copyStorage($path, $picture, $width = 600, $hight = null, $disk = "public")
    {
        $photo = Image::make($picture)
            ->resize($width, $hight, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->encode('jpg', 80);
        $pathPicture = $path . '/' . md5(now()->toString()) . '.jpg';
        Storage::disk($disk)->put($pathPicture, $photo);
        return $pathPicture;
    }

    public static function getKonfigurasiSystem($setting_name)
    {
        $data = Konfigruasi::whereSetting($setting_name);
        if ($data->count() > 0) {
            return $data->first()->value;
        } else {
            return false;
        }
    }

    public static function setHargaNet($barang_id, $qty_new, $harga_new)
    {
        $barang = Barang::withTrashed()->find($barang_id);
        if($barang){
            $harga_old = ($barang->harga_net??0) != 0 ? $barang->harga_net : $barang->pokok;
            $stock_old = self::getStock($barang_id);
            $harga_net = (($harga_old * $stock_old) + ($harga_new * $qty_new)) / ($qty_new + $stock_old);
            $barang->harga_net = $harga_net;
            $barang->pokok = $harga_new;
            $barang->save();
        }
    }
}
