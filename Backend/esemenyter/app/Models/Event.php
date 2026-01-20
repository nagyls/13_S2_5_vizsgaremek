<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [
        'type',
        'title',
        'description',
        'content',
        'users_id',
        'start_date',
        'end_date',
        'status',
    ];
}
