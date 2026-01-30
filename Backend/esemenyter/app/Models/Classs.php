<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classs extends Model
{
    //
    protected $fillable = [
        'users_id',
        'name',
        'grade',
        'establishments_id',
    ];
    public function getEstablishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}
