<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event_Feedback extends Model
{
    //
    protected $fillable = [
        'events_id',
        'answer',
        'users_id',
    ];
}
