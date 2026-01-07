<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event_message extends Model
{
    //
    protected $fillable = [
        'events_id',
        'users_id',
        'content',
    ];
}
