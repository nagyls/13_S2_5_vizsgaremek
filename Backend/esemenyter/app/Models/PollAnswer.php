<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollAnswer extends Model
{
    //
    protected $fillable = [
        'poll_id',
        'user_id',
        'poll_options_id',
    ];
}
