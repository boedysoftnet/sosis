<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'perusahaan_id',
        'barang_id',
        'satuan',
        'stock_sebelumnya',
        'qty',
        'harga',
        'alasan',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }

    public function getTotalAttribute()
    {
        return $this->qty * $this->harga;
    }

    public function userHistory()
    {
        return $this->morphMany(UserHistory::class, 'historyable');
    }

    public function stockHistory()
    {
        return $this->morphMany(StockHistory::class, 'stockhistoryable');
    }

    public function perusahaan(){
        return $this->belongsTo(Perusahaan::class)->withTrashed();
    }

    public function jurnal()
    {
        return $this->morphMany(Jurnal::class, 'jurnalable');
    }

}
