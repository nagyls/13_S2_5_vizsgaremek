<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Student extends Model
{
    //
    protected $fillable = [
        'alias',
        'establishment_id',
        'user_id',
    ];
    protected static function booted()
    {
        static::creating(function ($student) {
            if (empty($student->alias) && $student->user_id) {
                $user = User::find($student->user_id);
                if ($user) {
                    $student->alias = $user->name;
                }
            }
        });
    }
    public function getUser()
    {
        return $this->belongsTo(User::class);
    }

    public function getEstablishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}
