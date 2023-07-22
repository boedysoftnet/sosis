<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $appends = ['sub_total', 'total', 'total_setelah_ppn','qty_satuan'];
    protected $with = ['barang'];
    protected $fillable = [
        'penjualan_id',
        'barang_id',
        'harga',
        'qty',
        'satuan',
        'diskon',
        'ppn',
        'catatan',
        'satuan_isi',
        'pokok',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }

    public function getSubTotalAttribute()
    {
        return $this->qty_satuan * $this->harga;
    }

    public function getTotalAttribute()
    {
        return $this->getSubTotalAttribute() + $this->diskon;
    }

    public function getTotalSetelahPPNAttribute()
    {
        return $this->getTotalAttribute() + $this->ppn;
    }

    public function getQtySatuanAttribute()
    {
        return $this->qty/$this->satuan_isi;
    }

    public function penjualan(){
        return $this->belongsTo(Penjualan::class)->withTrashed();
    }
}
