<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;

    protected $with = [
        'barang',
    ];
    protected $fillable = [
        'pembelian_id',
        'barang_id',
        'harga',
        'satuan',
        'ppn',
        'qty',
        'diskon',
        'expaid',
        'catatan',
    ];

    protected $appends = [
        'sub_total',
        'total_setelah_ppn',
        'total'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class)->withTrashed();
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class)->without(['user', 'barangSatuan'])->withTrashed();
    }

    public function getSubTotalAttribute()
    {
        /*todo lihat disini kenapa hasil pembeliannya belum sesuai..*/
        return doubleval($this->harga * $this->qty);
    }

    public function getTotalAttribute()
    {
        return doubleval($this->sub_total + $this->diskon);
    }

    public function getTotalSetelahPPNAttribute()
    {
        return doubleval(($this->sub_total + $this->diskon) + $this->ppn);
    }

    public function userHistory()
    {
        return $this->morphMany(UserHistory::class, 'historyable');
    }

    public function stockHistory()
    {
        return $this->morphMany(StockHistory::class, 'stockhistoryable');
    }

}
