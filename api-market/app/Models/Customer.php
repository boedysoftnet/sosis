<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['url', 'join'];
    protected $fillable = [
        'nama',
        'alamat',
        'email',
        'telpon',
        'photo',
        'user_id',
        'catatan',
        'perusahaan_id',
    ];

    public function getUrlAttribute()
    {
        return $this->photo != null ? asset('storage/' . $this->photo) : asset('images/no-image.png');
    }

    public function getJoinAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class)->withTrashed();
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }
}
