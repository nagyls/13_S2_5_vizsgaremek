<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudent extends Model
{
    //
    protected $fillable = [
        'class_id',
        'user_id',
    ];
}
