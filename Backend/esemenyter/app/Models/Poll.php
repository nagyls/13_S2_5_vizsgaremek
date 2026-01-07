<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    //
    protected $fillable = [
        'events_id',
        'title',
        'users_id',
    ];
}
