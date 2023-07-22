<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'perusahaan_id',
        'barang_id',
        'kelompok',
        'qty',
        'stock_sebelumnya',
        'catatan',
    ];
}
