<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Establishment_Request extends Model
{
    //
    protected $fillable = [
        'users_id',
        'establishments_id',
        'status',
    ];
}
