<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangKategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'kategori', 'photo', 'deskripsi'];
    protected $appends=['jumlah_barang'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    public function getJumlahBarangAttribute()
    {
        return $this->barang()->count().' items';
    }
}
