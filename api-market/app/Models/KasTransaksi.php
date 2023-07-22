<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasTransaksi extends Model
{
    use HasFactory;

    protected $with=['bank','perusahaan','user','akunPerkiraan'];
    protected $fillable = [
        'perusahaan_id',
        'user_id',
        'akun_perkiraan_id',
        'bank_id',
        'kategori',
        'catatan',
        'nominal',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class)->withTrashed();
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function akunPerkiraan()
    {
        return $this->belongsTo(AkunPerkiraan::class)->withTrashed();
    }

    public function userHistory()
    {
        return $this->morphMany(UserHistory::class, 'historyable');
    }

    public function jurnal()
    {
        return $this->morphMany(Jurnal::class, 'jurnalable');
    }
}
