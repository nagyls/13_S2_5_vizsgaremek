<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event_Favourite extends Model
{
    //
    protected $fillable = [
        'events_id',
        'users_id',
    ];
}
