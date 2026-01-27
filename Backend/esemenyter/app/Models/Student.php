<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    protected $fillable = [
        'alias',
        'establishment_id',
        'user_id',
    ];
}
