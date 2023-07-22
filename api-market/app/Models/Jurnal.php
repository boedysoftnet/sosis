<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $with=['user'];
    protected $fillable = [
        'perusahaan_id',
        'user_id',
        'akun_perkiraan_id',
        'debet',
        'kredit',
        'kategori',
        'updated_at',
        'created_at',
        'catatan',
        'jurnalable_id',
        'jurnalable_type'
    ];

    public function akunPerkiraan()
    {
        return $this->belongsTo(AkunPerkiraan::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function perusahaan(){
        return $this->belongsTo(Perusahaan::class)->withTrashed();
    }
}
