<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event_shown extends Model
{
    //
    protected $fillable = [
        'events_id',
        'users_id',
        'classes_id',
    ];
}
