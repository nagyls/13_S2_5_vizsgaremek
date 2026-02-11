<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    //
    protected $table = 'classes';
    protected $fillable = [
        'user_id',
        'name',
        'grade',
        'establishment_id',
    ];
    public function getEstablishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}
