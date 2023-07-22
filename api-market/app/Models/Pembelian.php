<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Pembelian extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = [
        'sub_total',
        'diskon',
        'ppn',
        'total',
        'terbayar',
        'sisa_hutang',
        'total_setelah_ppn',
    ];
    protected $with = [
        'user',
        'bank',
        'supplier',
        'perusahaan',
        'pembelianDetail',
        'userHistory',
    ];
    protected $fillable = [
        'user_id',
        'supplier_id',
        'perusahaan_id',
        'bank_id',
        'no_faktur',
        'hutang',
        'tgl_tempo',
        'valid',
        'status',
        'catatan',
        'uang_tunai',
        'dana_transfer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class)->withTrashed();
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class)->withTrashed();
    }

    public function pembelianDetail()
    {
        return $this->hasMany(PembelianDetail::class);
    }

    public function getSubTotalAttribute()
    {
        return doubleval($this->pembelianDetail()->sum(DB::raw('harga*qty')) ?? 0);
    }

    public function getDiskonAttribute()
    {
        return doubleval($this->pembelianDetail()->sum(DB::raw('diskon')) ?? 0);
    }

    public function getTotalAttribute()
    {
        return doubleval(($this->sub_total + $this->diskon));
    }

    public function getTotalSetelahPPNAttribute()
    {
        return doubleval(($this->sub_total + $this->diskon) + $this->ppn);
    }

    public function getPpnAttribute()
    {
        return doubleval($this->pembelianDetail()->sum(DB::raw('ppn')) ?? 0);
    }

    public function userHistory()
    {
        return $this->morphMany(UserHistory::class, 'historyable');
    }

    public function jurnal()
    {
        return $this->morphMany(Jurnal::class, 'jurnalable');
    }

    public function hutangSetoran()
    {
        return $this->hasMany(HutangSetoran::class);
    }

    public function getSisaHutangAttribute()
    {
        return $this->hutang - $this->hutangSetoran()->sum('jumlah');
    }

    public function getTerbayarAttribute()
    {
        return $this->hutangSetoran()->sum('jumlah');
    }
}
