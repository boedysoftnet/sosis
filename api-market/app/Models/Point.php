<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'penjualan_id',
        'perusahaan_id',
        'jenis',
        'total',
        'nilai_kelipatan',
        'point',
        'keterangan',
    ];
}
