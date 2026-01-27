<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personel extends Model
{
    //
    protected $fillable = [
        'role',
        'establishment_id',
        'user_id',
    ];
}
