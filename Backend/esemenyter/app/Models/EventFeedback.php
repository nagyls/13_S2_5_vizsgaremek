<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFeedback extends Model
{
    // explicit table name — migration created "event_feedbacks"
    protected $table = 'event_feedbacks';

    protected $fillable = [
        'event_id',
        'answer',
        'user_id',
    ];
}
