<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AkunKelompok extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends=[
        'golongan'
    ];
    protected $fillable = [
        'user_id',
        'akun_golongan_id',
        'kelompok',
    ];

    public function akunGolongan()
    {
        return $this->belongsTo(AkunGolongan::class)->orderBy('id')->withTrashed();
    }

    public function getGolonganAttribute(){
        return $this->akunGolongan->golongan;
    }

}
