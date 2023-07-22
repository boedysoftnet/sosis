<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'historyable_id',
        'historyable_type',
        'body',
    ];
    public function historyable(){
        return $this->morphTo();
    }
}
