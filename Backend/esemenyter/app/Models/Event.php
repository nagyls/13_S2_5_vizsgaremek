<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [
        'staff_id',
        'user_id',
        'establishment_id',
        'type',
        'target_group',
        'target_class_ids',
        'target_grade_ids',
        'title',
        'description',
        'content',
        'chat_enabled',
        'start_date',
        'end_date',
        'status',

        //SZAKKÖR MÓDOSÍTÁS
        'is_recurring',
        'recurrence_frequency',
        'recurrence_until',
        'recurrence_parent_event_id',
        'cancelled_at',
        //
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'target_class_ids' => 'array',
        'target_grade_ids' => 'array',

        //SZAKKÖR MÓDOSÍTÁS
        'recurrence_until' => 'datetime',
        'is_recurring' => 'boolean',
        'cancelled_at' => 'datetime',
        //
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function class()
    {
        return $this->belongsToMany(ClassModel::class);
    }
}
