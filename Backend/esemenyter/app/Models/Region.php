<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    //
    // Disable automatic timestamps as migration does not add created_at/updated_at
    public $timestamps = false;
    protected $fillable = [
        'title',
    ];

    public function innerregions()
    {
        return $this->hasMany(InnerRegion::class);
    }
}
