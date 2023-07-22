<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiutangSetoran extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'penjualan_id',
        'bank_id',
        'perusahaan_id',
        'jumlah',
        'penerima',
        'catatan',
        'tgl_setoran',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class)->withTrashed();
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class)->withTrashed();
    }

    public function jurnal()
    {
        return $this->morphMany(Jurnal::class, 'jurnalable');
    }

    public function userHistory()
    {
        return $this->morphMany(UserHistory::class, 'historyable');
    }
    public function perusahaan(){
        return $this->belongsTo(Perusahaan::class)->withTrashed();
    }
}
