<?php

namespace App\Models;

use App\Helpers\Boedysoft;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Produksi extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['total', 'varian_produksi', 'estimasi_hpp', 'total_setelah_ppn', 'ppn'];
    protected $with = ['produksiDetail', 'barang', 'perusahaan', 'user'];
    protected $fillable = [
        'perusahaan_id',
        'barang_id',
        'tgl_produksi',
        'tgl_selesai',
        'estimasi',
        'hasil_produksi',
        'catatan',
        'user_id',
        'valid',
        'ppn',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function produksiDetail()
    {
        return $this->hasMany(ProduksiDetail::class);
    }

    public function getTotalAttribute()
    {
        return round($this->produksiDetail()->sum(DB::raw('(jumlah/satuan_isi)*harga')), 0);
    }

    public function getTotalSetelahPPNAttribute()
    {
        return round($this->getTotalAttribute()+$this->getPPNAttribute(), 0);
    }

    public function getPPNAttribute()
    {
        if (Boedysoft::getKonfigurasiSystem('produksi non ppn')) {
            return 0;
        } else {
            return round($this->produksiDetail()->sum('ppn'), 0);
        }
    }

    public function getEstimasiHPPAttribute()
    {
        if (Boedysoft::getKonfigurasiSystem('produksi non ppn')) {
            return $this->getTotalAttribute() > 0 ? round($this->getTotalAttribute() / ($this->estimasi ?? 1)) : 0;
        } else {
            return $this->getTotalSetelahPPNAttribute() > 0 ? round($this->getTotalSetelahPPNAttribute() / ($this->estimasi ?? 1)) : 0;
        }
    }

    public function getVarianProduksiAttribute()
    {
        return $this->produksiDetail()->count();
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }

    public function stockHistory()
    {
        return $this->morphMany(StockHistory::class, "stockhistoryable");
    }

    public function jurnal()
    {
        return $this->morphMany(Jurnal::class, "jurnalable");
    }
}
