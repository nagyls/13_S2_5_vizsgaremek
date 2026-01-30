<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    //
    protected $fillable = [
        'title',
        'inner_region_id',
        'number'
    ];
    public function getInnerRegion()
    {
        return $this->belongsTo(InnerRegion::class);
    }
}
