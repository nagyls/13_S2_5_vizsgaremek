<?php

namespace App\Http\Controllers;


use App\Models\Establishment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ClassModel;

class StaffController extends Controller
{
    //get staff
    public function getStaff($establishmentId)
    {
        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json([
                'success' => false,
                'message' => 'Intézmény nem található!'
            ], 400);
        }
        $staff = User::join('staffs', 'users.id', '=', 'staffs.user_id')
            ->where('staffs.establishment_id', $establishmentId)
            ->select('users.*')
            ->distinct()
            ->get();

        return response()->json([
            'data' => $staff
        ]);
    }
}
