<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class InnerRegion extends Model
{
    // Disable automatic timestamps as migration does not add created_at/updated_at
    public $timestamps = false;
    protected $fillable = [
        'title',
        'region_id',
    ];
    public function settlements()
    {
        return $this->hasMany(Settlement::class);
    }
    public function getRegion()
    {
        return $this->belongsTo(Region::class);
    }
}
