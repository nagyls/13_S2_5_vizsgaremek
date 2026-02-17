<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentRequest extends Model
{
    //
    protected $fillable = [
        'users_id',
        'role',
        'establishment_id',
        'status',
    ];
}
