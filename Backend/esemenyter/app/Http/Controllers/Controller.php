<?php

namespace App\Http\Controllers;

use App\Models\Staff;

abstract class Controller
{
    //
    protected function isAdminEstablishment($userId, $establishmentId)
    {
        return Staff::where('user_id', $userId)
            ->where('establishment_id', $establishmentId)
            ->where('role', 'admin')
            ->exists();
    }
}
