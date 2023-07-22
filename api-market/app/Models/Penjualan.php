<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Penjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['akumulasi_point', 'sub_total', 'total', 'total_setelah_ppn', 'ppn', 'diskon', 'nama_operator', 'total_pokok', 'sisa_piutang', 'terbayar', 'jatuh_tempo', 'kembalian'];
    protected $with = ['penjualanDetail', 'customer', 'bank', 'perusahaan'];
    protected $fillable = [
        'user_id',
        'customer_id',
        'bank_id',
        'perusahaan_id',
        'piutang',
        'uang_tunai',
        'dana_transfer',
        'catatan',
        'status',
        'tgl_tempo',
        'ppn_persentase',
        'jumlah_uang',
        'valid',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class)->withTrashed();
    }

    public function penjualanDetail()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function getSubTotalAttribute()
    {
        return $this->penjualanDetail()->sum(DB::raw('(qty/satuan_isi)*harga'));
    }

    public function getDiskonAttribute()
    {
        return $this->penjualanDetail()->sum('diskon');
    }

    public function getTotalAttribute()
    {
        return $this->getSubTotalAttribute() + $this->getDiskonAttribute();
    }

    public function getPPNAttribute()
    {
        return $this->penjualanDetail()->sum('ppn');
    }

    public function getTotalPokokAttribute()
    {
        return $this->penjualanDetail()->sum(DB::raw('qty*pokok'));
    }

    public function getTotalSetelahPPNAttribute()
    {
        return $this->getTotalAttribute() + $this->getPPNAttribute();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getNamaOperatorAttribute()
    {
        return $this->user->name;
    }

    public function userHistory()
    {
        return $this->morphMany(UserHistory::class, 'historyable');
    }

    public function stockHistory()
    {
        return $this->morphMany(StockHistory::class, 'stockhistoryable');
    }

    public function jurnal()
    {
        return $this->morphMany(Jurnal::class, 'jurnalable');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class)->withTrashed();
    }

    public function getSisaPiutangAttribute()
    {
        return $this->piutang - ($this->piutangSetoran()->sum('jumlah'));
    }

    public function getTerbayarAttribute()
    {
        return $this->piutangSetoran()->sum('jumlah') + $this->jumlah_uang;
    }

    public function getJatuhTempoAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function getKembalianAttribute()
    {
        return $this->getTotalSetelahPPNAttribute() - $this->jumlah_uang;
    }

    public function piutangSetoran()
    {
        return $this->hasMany(PiutangSetoran::class);
    }

    public function point()
    {
        return $this->hasMany(Point::class);
    }

    public function getAkumulasiPointAttribute()
    {
        return collect(DB::select("select round(sum(point)-0.49) as point from points where penjualan_id in(select id from penjualans where customer_id='".$this->attributes['customer_id']."')"))->first()->point??0;
    }
}
