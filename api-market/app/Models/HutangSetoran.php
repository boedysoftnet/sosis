<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HutangSetoran extends Model
{
    use HasFactory;

    protected $with=['pembelian','user'];
    protected $fillable = [
        'user_id',
        'pembelian_id',
        'bank_id',
        'perusahaan_id',
        'jumlah',
        'penerima',
        'catatan',
        'tgl_setoran',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class)->withTrashed();
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
    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }

}
