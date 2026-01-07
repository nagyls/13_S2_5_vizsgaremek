<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll_option extends Model
{
    //
    protected $fillable = [
        'polls_id',
        'option_text',
    ];
}
