<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EventMessage extends Model
{
    //
    protected $fillable = [
        'event_id',
        'user_id',
        'content',
        'is_important',
    ];

    protected $casts = [
        'is_important' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
