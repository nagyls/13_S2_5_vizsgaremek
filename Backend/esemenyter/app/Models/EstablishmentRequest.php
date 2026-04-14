<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EstablishmentRequest extends Model
{
    //
    protected $fillable = [
        'user_id',
        'role',
        'alias',
        'establishment_id',
        'status',
    ];
    const UPDATED_AT = null;

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'establishment_id');
    }

    public function userFromId()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
