<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Staff extends Model
{
    //
    protected $table = 'staffs';
    protected $fillable = [
        'role',
        'alias',
        'establishment_id',
        'user_id',
    ];

    protected static function booted()
    {
        static::creating(function ($staff) {
            if (empty($staff->alias) && $staff->user_id) {
                $user = User::find($staff->user_id);
                if ($user) {
                    $staff->alias = $user->name;
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}
