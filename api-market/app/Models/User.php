<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $with = ['perusahaan', 'departement'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'departement_id',
        'photo',
        'perusahaan_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends=['url'];
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class)->withTrashed();
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class)->withTrashed();
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class);
    }

    public function getUrlAttribute()
    {
        if ($this->attributes['photo']) {
            return asset('storage/' . $this->attributes['photo']);
        } else {
            return 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
        }
    }

    public function userHistory()
    {
        return $this->morphMany(UserHistory::class, "historyable");
    }

}
