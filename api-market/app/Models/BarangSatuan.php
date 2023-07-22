<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangSatuan extends Model
{
    use HasFactory;

    protected $fillable=[
        'perusahaan_id',
        'barang_id',
        'satuan',
        'isi',
        'harga',
    ];
}
