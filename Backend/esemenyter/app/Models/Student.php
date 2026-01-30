<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    protected $fillable = [
        'alias',
        'establishment_id',
        'user_id',
    ];

    public function getUser()
    {
        return $this->belongsTo(User::class);
    }

    public function getEstablishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}
