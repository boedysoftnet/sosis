<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $with = ['user'];
    protected $fillable = [
        'user_id',
        'nama',
        'alamat',
        'email',
        'telpon',
        'ppn',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
