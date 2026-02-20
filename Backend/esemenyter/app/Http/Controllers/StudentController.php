<?php

namespace App\Http\Controllers;


use App\Models\Establishment;
use App\Models\User;

class StudentController extends Controller
{
    //
    public function getStudents($establishmentId)
    {
        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json([
                'success' => false,
                'message' => 'Intézmény nem található!'
            ], 400);
        }
        $staffs = User::join('students', 'users.id', '=', 'students.user_id')
            ->where('students.establishment_id', $establishmentId)
            ->select('users.*')
            ->distinct()
            ->get();

        return response()->json([
            'data' => $staffs
        ]);
    }
}
