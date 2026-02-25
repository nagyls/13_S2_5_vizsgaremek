<?php

namespace App\Http\Controllers;


use App\Models\Establishment;
use App\Models\User;
use App\Models\Students;
use App\Models\ClassStudent;
use Illuminate\Http\Request;
use App\Models\ClassModel;

class StudentController extends Controller
{
    // Diákok lekérdezése
    public function getStudents($establishmentId)
    {
        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json([
                'success' => false,
                'message' => 'Intézmény nem található!'
            ], 400);
        }
        $students = User::join('students', 'users.id', '=', 'students.user_id')
            ->where('students.establishment_id', $establishmentId)
            ->select('users.*')
            ->distinct()
            ->get();

        return response()->json([
            'data' => $students
        ]);
    }
    // Diák hozzáadása osztályhoz
    public function storeInClass(Request $request, $establishmentId, $classId)
    {
        $user = $request->user();
        request()->validate([
            'student_id'   => 'required|array',
            'student_id.*' => 'integer|exists:users,id',
        ],[
            'student_id.required' => 'A student_id mező kötelező.',
            'student_id.array' => 'A student_id mezőnek tömbnek kell lennie.',
            'student_id.*.integer' => 'Minden student_id értéknek egész számnak kell lennie.',
            'student_id.*.exists' => 'Minden student_id értéknek léteznie kell a users táblában.'
        ]);
        
        $studentId = request()->input('student_id');
        
        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json([
                'message' => 'Intézmény nem található!'
            ], 400);
        }

        $class = ClassModel::find($classId);
        if (!$class) {
            return response()->json([
                'message' => 'Osztály nem található!'
            ], 400);
        }

        $existing = ClassStudent::where('class_id', $classId)
            ->whereIn('user_id', $studentId)
            ->pluck('user_id')
            ->toArray();

        $toInsert = array_diff($studentId, $existing);

        foreach ($toInsert as $id) {
            ClassStudent::create([
                'user_id' => $id, 
                'class_id' => $classId
            ]);
        }

        if (!empty($wrongStudents)) {
            return response()->json([
                'message' => "Diák (ID: " . implode(', ', $wrongStudents) . ") már tagja az osztálynak!"
            ], 409);
        }

        return response()->json([
            'message' => 'Diák(ok) hozzáadva az osztályhoz!'
        ]);
    }

}
