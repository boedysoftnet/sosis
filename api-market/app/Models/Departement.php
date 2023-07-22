<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departement extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'rules' => 'array'
    ];
    protected $fillable = [
        'owner_id',
        'nama',
        'rules',
    ];

    public function owner(){
        return $this->belongsTo(Owner::class)->withTrashed();
    }
}
