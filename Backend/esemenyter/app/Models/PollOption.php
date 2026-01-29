<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    //
    protected $fillable = [
        'polls_id',
        'option_text',
    ];
}
