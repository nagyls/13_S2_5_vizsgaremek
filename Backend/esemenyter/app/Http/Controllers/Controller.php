<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Staff;
use App\Models\Student;

abstract class Controller
{
    // Eldönti, hogy egy esemény lezártnak számít-e státusz vagy dátum alapján.
    protected function isEventClosed(Event $event): bool
    {
        if ($event->status === 'ended') {
            return true;
        }

        return $event->end_date !== null && $event->end_date->lt(now());
    }

    // Ellenőrzi, hogy a felhasználó admin az adott intézményben
    protected function isAdminEstablishment($userId, $establishmentId)
    {
        return Staff::where('user_id', $userId)
            ->where('establishment_id', $establishmentId)
            ->where('role', 'admin')
            ->exists();
    }
    // Ellenőrzi, hogy a felhasználó staff tag az adott intézményben
    protected function isStaffEstablishment($userId, $establishmentId)
    {
        return Staff::where('user_id', $userId)
            ->where('establishment_id', $establishmentId)
            ->exists();
    }
    // Ellenőrzi, hogy a felhasználó tagja az intézménynek
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
    // Törli a felhasználót, ha még nem erősítette meg az email címét
    protected function deleteUnverifiedUser($user)
    {
        if ($user && !$user->hasVerifiedEmail()) {
            $user->delete();
        }
    }
}
