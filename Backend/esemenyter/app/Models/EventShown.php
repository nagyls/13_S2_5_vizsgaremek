<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventShown extends Model
{
    //
    protected $table = 'event_shows';
    protected $fillable = [
        'event_id',
        'user_id',
        'establishment_id',
    ];
    const UPDATED_AT = null;
}
