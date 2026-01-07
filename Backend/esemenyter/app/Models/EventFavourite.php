<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFavourite extends Model
{
    //
    protected $fillable = [
        'events_id',
        'users_id',
    ];
}
