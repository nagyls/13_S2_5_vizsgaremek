<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Establishment extends Model
{
    //
    protected $fillable = [
        'title',
        'description',
        'settlements_id',
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function personels()
    {
        return $this->hasMany(Personel::class);
    }
}
