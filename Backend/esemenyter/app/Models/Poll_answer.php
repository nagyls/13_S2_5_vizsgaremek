<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll_answer extends Model
{
    //
    protected $fillable = [
        'polls_id',
        'users_id',
        'poll_options_id',
    ];
}
