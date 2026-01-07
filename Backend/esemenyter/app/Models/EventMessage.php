<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMessage extends Model
{
    //
    protected $fillable = [
        'events_id',
        'users_id',
        'content',
    ];
}
