<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class AkunPerkiraan extends Model
{
    use HasFactory, SoftDeletes;

    protected $with=['jurnal'];
    protected $fillable = [
        'user_id',
        'akun_kelompok_id',
        'nama_akun',
        'keterangan',
    ];

    protected $appends = [
        'kelompok',
        'golongan',
        'saldo_akun',
        'debet',
        'kredit',
        'kredit_sum',
        'debet_sum',
    ];


    public function akunKelompok()
    {
        return $this->belongsTo(AkunKelompok::class)->withTrashed();
    }

    public function getKelompokAttribute()
    {
        return $this->akunKelompok->id . '~' . $this->akunKelompok->kelompok;
    }

    public function getGolonganAttribute()
    {
        return $this->akunKelompok->akun_golongan_id . '~' . $this->akunKelompok->golongan;
    }

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class);
    }

    public function getSaldoAkunAttribute()
    {
        return $this->jurnal()->sum(DB::raw('debet-kredit'));
    }

    public function getDebetAttribute()
    {
        $total = $this->jurnal()->sum(DB::raw('debet-kredit'));
        return $total > 0 ? $total : 0;
    }

    public function getKreditAttribute()
    {
        $total = $this->jurnal()->sum(DB::raw('kredit-debet'));
        return $total > 0 ? $total : 0;
    }

    public function getKreditSumAttribute()
    {
        $total = $this->jurnal()->sum(DB::raw('kredit'));
        return $total > 0 ? $total : 0;
    }
    public function getDebetSumAttribute()
    {
        $total = $this->jurnal()->sum(DB::raw('debet'));
        return $total > 0 ? $total : 0;
    }
}
