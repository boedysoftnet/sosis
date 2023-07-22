<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiDetail extends Model
{
    use HasFactory;

    protected $appends = ['total','total_setelah_ppn'];
    protected $with = ['barang'];
    protected $fillable = [
        'produksi_id',
        'barang_id',
        'jumlah',
        'satuan',
        'ppn',
        'satuan_isi',
        'harga',
    ];

    function barang()
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }

    function getTotalAttribute()
    {
        return ($this->jumlah / $this->satuan_isi) * $this->harga;
    }
    function getTotalSetelahPPNAttribute()
    {
        return $this->getTotalAttribute()+$this->ppn;
    }

    function getHargaAttribute()
    {
        return $this->attributes['harga'] * $this->attributes['satuan_isi'];
    }
    public function stockHistory()
    {
        return $this->morphMany(StockHistory::class, 'stockhistoryable');
    }

}
