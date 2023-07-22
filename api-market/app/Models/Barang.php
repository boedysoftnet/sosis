<?php

namespace App\Models;

use App\Helpers\Boedysoft;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, SoftDeletes;

    /*todo jangan lupa buat filter untuk owner id di barang agar terpisah*/
    protected $with = ['barangKategori', 'user', 'barangSatuan', 'stock'];
    protected $fillable = ['barang_kategori_id', 'nama_barang', 'pokok', 'photo', 'user_id','type_jasa','harga_net'];
    protected $appends = ['satuan', 'harga_jual', 'sisa_stock', 'pokok_terakhir'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function barangKategori()
    {
        return $this->belongsTo(BarangKategori::class)->withTrashed();
    }

    public function barangSatuan()
    {
        return $this->hasMany(BarangSatuan::class);
    }

    public function stock()
    {
        return $this->hasMany(Stock::class);
    }

    public function pembelianDetail()
    {
        return $this->hasMany(PembelianDetail::class);
    }

    public function getSatuanAttribute()
    {
        return $this->barangSatuan()->first()->satuan;
    }

    public function getHargaJualAttribute()
    {
        return $this->barangSatuan()->first()->harga;
    }

    public function getSisaStockAttribute()
    {
        if ($this->stock->count()) {
            $stock_gantung = PenjualanDetail::whereHas('penjualan', function ($q) {
                $q->where('status', 'preorder');
                $q->whereNotNull('valid');
                $q->where('perusahaan_id', request()->user()->perusahaan_id);
            })->whereBarangId($this->id)->sum('qty');
            $stock=$this->stock()->where('perusahaan_id',request()->user()->perusahaan_id)->first()->stock??0;
            return $stock - $stock_gantung;
        } else {
            return 0;
        }
    }

    public function userHistory()
    {
        return $this->morphMany(UserHistory::class, 'historyable');
    }


    public function getPokokAttribute()
    {
        $config = (boolean)Boedysoft::getKonfigurasiSystem('metode average');
        if ($config) {
            return ($this->attributes['harga_net']??0) ? $this->attributes['harga_net'] : $this->attributes['pokok'];
        } else {
            return $this->attributes['pokok'];
        }
    }

    public function getPokokTerakhirAttribute()
    {
        return $this->attributes['pokok'];
    }

    public function penjualanDetail()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    public function stockHistory()
    {
        return $this->morphMany(StockHistory::class, 'stockhistoryable');
    }

    public function jurnal()
    {
        return $this->morphMany(Jurnal::class, 'jurnalable');
    }

}
