<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventShown extends Model
{
    //
    protected $fillable = [
        'event_id',
        'user_id',
    ];
    const UPDATED_AT = null;
}
