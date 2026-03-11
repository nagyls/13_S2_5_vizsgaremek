<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRequest extends Model
{
    //
    protected $fillable = [
        'event_id',
        'establishment_id',
    ];
    public function thisevent()
    {
        return $this->belongsTo(Event::class);
    }
    public function thisestablishment()
    {
        return $this->belongsTo(Establishment::class);
    }

}
