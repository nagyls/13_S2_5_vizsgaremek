<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFeedback extends Model
{
    //
    protected $fillable = [
        'events_id',
        'answer',
        'users_id',
    ];
}
