<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Student;

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
    protected function isStaffEstablishment($userId, $establishmentId)
    {
        return Staff::where('user_id', $userId)
            ->where('establishment_id', $establishmentId)
            ->exists();
    }
    protected function isMemberEstablishment($userId, $establishmentId)
    {
        return Staff::where('user_id', $userId)
            ->where('establishment_id', $establishmentId)
            ->exists()
            || 
            Student::where('user_id', $userId)
            ->where('establishment_id', $establishmentId)
            ->exists();
    }
    protected function deleteUnverifiedUser($user)
    {
        if ($user && !$user->hasVerifiedEmail()) {
            $user->delete();
        }
    }
}
