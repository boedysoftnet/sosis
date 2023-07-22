<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perusahaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends=['icon','nama_owner'];
    protected $fillable = [
        'nama',
        'alamat',
        'telpon',
        'email',
        'photo',
        'owner_id',
        'icon',
        'keterangan',
    ];

    public function stock(){
        return $this->hasMany(Stock::class);
    }

    public function owner(){
        return $this->belongsTo(Owner::class)->withTrashed();
    }

    public function getIconAttribute(){
        if($this->attributes['icon']){
            return asset('storage/'.$this->attributes['icon']);
        }else{
            return asset("images/no-image.jpg");
        }
    }
    public function getNamaOwnerAttribute(){
        return $this->owner->nama;
    }
}
