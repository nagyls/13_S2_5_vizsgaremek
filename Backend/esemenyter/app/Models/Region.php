<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    //
    protected $fillable = [
        'title',
    ];

    public function innerregions()
    {
        return $this->hasMany(InnerRegion::class);
    }
}
