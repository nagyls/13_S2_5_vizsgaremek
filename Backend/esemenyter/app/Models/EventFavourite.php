<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFavourite extends Model
{
    //
    protected $fillable = [
        'event_s_id',
        'user_id',
    ];
}
