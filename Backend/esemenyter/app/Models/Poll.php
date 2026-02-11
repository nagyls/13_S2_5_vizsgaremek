<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    //
    protected $fillable = [
        'event_id',
        'title',
        'user_id',
    ];
}
