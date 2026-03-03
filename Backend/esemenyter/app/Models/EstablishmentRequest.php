<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentRequest extends Model
{
    //
    protected $fillable = [
        'user_id',
        'role',
        'establishment_id',
    ];
    const UPDATED_AT = null;

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'establishment_id');
    }
}
