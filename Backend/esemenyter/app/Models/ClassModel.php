<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    //
    protected $table = 'classes';
    protected $fillable = [
        'establishment_id', 
        'user_id', 
        'grade', 
        'name'
    ];

    protected $casts = [
        'name' => 'string',
        'grade' => 'integer',
        'establishment_id' => 'integer',
    ];

    public function getEstablishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}
