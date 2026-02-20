<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Staff;

class Establishment extends Model
{
    //
    protected $fillable = [
        'title',
        'description',
        'user_id',
        'settlement_id',
        'website',
        'email',
        'phone',
        'address',
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function Staffs()
    {
        return $this->hasMany(Staff::class);
    }
    public function scopeWithoutClasses($query)
    {
        return $query->whereDoesntHave('classes');
    }
}
