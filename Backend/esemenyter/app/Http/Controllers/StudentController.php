<?php

namespace App\Http\Controllers;


use App\Models\Establishment;
use App\Models\User;
use App\Models\Students;
use App\Models\ClassStudent;
use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Student;

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
            ->select('users.*', 'students.alias', 'students.id as student_id')
            ->distinct()
            ->get();

        return response()->json([
            'data' => $students
        ]);
    }
    // Diák hozzáadása osztályhoz
    public function storeInClass(Request $request)
    {
        $user = $request->user();
        request()->validate([
            'establishment_id' => 'required|integer|exists:establishments,id',
            'class_id' => 'required|integer|exists:classes,id',
            'student_id'   => 'required|array',
            'student_id.*' => 'integer|exists:students,id',
        ],[
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.integer' => 'Az intézmény azonosítónak egész számnak kell lennie.',
            'establishment_id.exists' => 'Intézmény nem található!',
            'class_id.required' => 'Az osztály azonosító megadása kötelező.',
            'class_id.integer' => 'Az osztály azonosítónak egész számnak kell lennie.',
            'class_id.exists' => 'Osztály nem található!',
            'student_id.required' => 'A student_id mező kötelező.',
            'student_id.array' => 'A student_id mezőnek tömbnek kell lennie.',
            'student_id.*.integer' => 'Minden student_id értéknek egész számnak kell lennie.',
            'student_id.*.exists' => 'A student_id nem létezik.'
        ]);
        $establishmentId = $request->input('establishment_id');
        $classId = $request->input('class_id');
        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $establishment = Establishment::find($establishmentId);
        $class = ClassModel::find($classId);

        if ($class && $establishment && $class->establishment_id != $establishmentId) {
            return response()->json([
                'errors' => 'Az osztály nem tartozik az intézményhez!'
            ], 400); 
        }

        $studentIds = $request->input('student_id');
        
        // Check all students exist and belong to the establishment in ONE query
        $validStudentsIds = Student::whereIn('id', $studentIds)
            ->where('establishment_id', $establishmentId)
            ->pluck('id')
            ->toArray();
            
        if (count($validStudentsIds) !== count($studentIds)) {
            $invalidIds = array_diff($studentIds, $validStudentsIds);
            return response()->json([
                'errors' => "Diák ID(k): " . implode(', ', $invalidIds) . " nem létezik vagy nem tartozik az intézményhez!"
            ], 400);
        }

        $userIds = Student::whereIn('id', $studentIds)
            ->pluck('user_id')
            ->toArray();

        $alreadyMember= ClassStudent::where('class_id', $classId)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->toArray();


        $toInsert = array_diff($userIds, $alreadyMember);
        $alreadyExists = array_intersect($userIds, $alreadyMember);

        foreach ($toInsert as $userId) {
            ClassStudent::create([
                'user_id' => $userId, 
                'class_id' => $classId
            ]);
        }

        if (!empty($alreadyExists)) {
            return response()->json([
                'message' => "Diák (User ID: " . implode(', ', $alreadyExists) . ") már tagja az osztálynak!"
            ], 409);
        }

        return response()->json([
            'message' => 'Diák(ok) hozzáadva az osztályhoz!'
        ]);
    }

}
