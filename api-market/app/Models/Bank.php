<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory, SoftDeletes;

    protected $with = ['user'];

    protected $fillable=[
        'user_id',
        'nama_bank',
        'alamat',
        'telpon',
        'email',
        'catatan',
        'atas_nama',
        'nomor_rekening'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
