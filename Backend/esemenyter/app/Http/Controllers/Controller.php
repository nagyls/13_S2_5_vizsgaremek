<?php

namespace App\Http\Controllers;

use App\Models\Personel;

abstract class Controller
{
    //
    protected function isAdminEstablishment($userId, $establishmentId)
    {
        return Personel::where('user_id', $userId)
            ->where('establishment_id', $establishmentId)
            ->where('role', 'admin')
            ->exists();
    }
}
