<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventShown extends Model
{
    //
    protected $table = 'event_shows';
    protected $fillable = [
        'is_favourite',
        'event_id',
        'user_id',
        'answer',
        'establishment_id',
    ];
    const UPDATED_AT = null;
}
