<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event_Shown extends Model
{
    //
    protected $fillable = [
        'events_id',
        'users_id',
        'classes_id',
    ];
}
