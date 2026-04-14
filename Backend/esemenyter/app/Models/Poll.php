<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Poll extends Model
{
    protected $fillable = [
        'event_id',
        'title',
        'user_id',
        'start_date',
        'deadline',
        'is_timed',
        'hidden_results',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'is_timed' => 'boolean',
        'hidden_results' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    public function answers()
    {
        return $this->hasMany(PollAnswer::class);
    }

    public function hasStarted()
    {
        if (!$this->start_date instanceof Carbon) {
            return true;
        }

        return $this->start_date->isPast();
    }

    public function hasEnded()
    {
        if ($this->is_active === false) {
            return true;
        }

        if (!$this->is_timed || !$this->deadline instanceof Carbon) {
            return false;
        }

        return $this->deadline->copy()->endOfDay()->isPast();
    }

    public function resultsAreVisible()
    {
        return !$this->hidden_results || $this->hasEnded();
    }
}
