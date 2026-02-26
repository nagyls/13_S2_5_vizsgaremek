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
    public function updateClassTeacher(Request $request)
    {
        request()->validate([
            'establishment_id' => 'required|integer|exists:establishments,id',
            'class_id' => 'required|integer|exists:class_models,id',
            'teacher_id'   => 'required|integer|exists:users,id',
        ],[
            'teacher_id.required' => 'A teacher_id mező kötelező.',
            'teacher_id.integer' => 'A teacher_id értéknek egész számnak kell lennie.',
            'teacher_id.exists' => 'A teacher_id értéknek léteznie kell a users táblában.'
        ]);
        $user = $request->user();
        $establishmentId = $request->input('establishment_id');
        $classId = $request->input('class_id');
        
        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json([
                'message' => 'Intézmény nem található!'
            ], 400);
        }
        if (!$this->isStaffEstablishment($request->teacher_id, $establishmentId)) {
            return response()->json([
                'message' => 'A megadott tanár nem tagja az intézménynek!'
            ], 400);
        }
        $class = ClassModel::find($classId);
        if (!$class) {
            return response()->json([
                'message' => 'Osztály nem található!'
            ], 400);
        }
        // Tanár hozzárendelése az osztályhoz
        $class->user_id = $request->teacher_id;
        $class->save();

        return response()->json([
            'message' => 'Tanár sikeresen hozzárendelve az osztályhoz!'
        ]);
    }
}
