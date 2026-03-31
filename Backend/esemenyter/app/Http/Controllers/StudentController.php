<?php

namespace App\Http\Controllers;


use App\Models\Establishment;
use App\Models\User;
use App\Models\Students;
use App\Models\ClassStudent;
use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Event;
use App\Models\EventShown;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
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

        if (!empty($toInsert)) {
            $this->retroactiveEventShow($establishmentId, $classId, $toInsert);
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
    public function removeFromClass(Request $request)
    {
        $user = $request->user();
        request()->validate([
            'establishment_id' => 'required|integer|exists:establishments,id',
            'class_id' => 'required|integer|exists:classes,id',
            'student_id'   => 'required|array',
            'student_id.*' => 'integer|exists:students,id',
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

        ClassStudent::where('class_id', $classId)
            ->whereIn('user_id', $userIds)
            ->delete();

        return response()->json([
            'message' => 'Diák(ok) eltávolítva az osztályból!'
        ]);
    }
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
            ->select('users.id','students.id as student_id','users.name', 'students.alias','users.email', 'students.created_at','students.updated_at')
            ->distinct()
            ->get();

        return response()->json([
            'data' => $students
        ]);
    }

    public function removeStudents(Request $request)
    {
        $user = $request->user();
        request()->validate([
            'establishment_id' => 'required|integer|exists:establishments,id',
            'student_id'   => 'required|array',
            'student_id.*' => 'integer|exists:students,id',
        ]);
        $establishmentId = $request->input('establishment_id');

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $studentIds = $request->input('student_id');

   
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

        ClassStudent::whereIn('user_id', $userIds)
            ->delete();

        return response()->json([
            'message' => 'Diák(ok) eltávolítva az osztályból!'
        ]);
    }

    public function setAlias(Request $request, int $establishmentId, int $memberId)
    {
        $user = $request->user();

        $validated = $request->validate([
            'alias' => 'nullable|string|max:255',
        ]);

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }
        if (!$this->isMemberOfEstablishment($memberId, $establishmentId)) {
            return response()->json(['errors' => 'A megadott tag nem tartozik az intézményhez!'], 400);
        }
        $staff = Staff::find($memberId);
        if ($staff) {
            $staff->alias = $validated['alias'];
            $staff->save();
        }else {
             return response()->json(['errors' => 'A megadott tag nem diák!'], 400);
        }

        
        $student = Student::find($memberId);
        if (!$student || $student->establishment_id != $establishmentId) {
            return response()->json(['errors' => 'A diák nem tartozik az intézményhez!'], 400);
        }

        $student->alias = $validated['alias'];
        $student->save();

        return response()->json(['message' => 'Álnév frissítve']);
    }

    public function updateClassStudents(Request $request, int $establishmentId, int $classId)
    {
        $user = $request->user();

        $validated = $request->validate([
            'add' => 'array',
            'add.*' => 'integer|exists:students,id',
            'remove' => 'array',
            'remove.*' => 'integer|exists:students,id',
        ]);

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $class = ClassModel::find($classId);
        if (!$class || $class->establishment_id != $establishmentId) {
            return response()->json(['errors' => 'Az osztály nem tartozik az intézményhez!'], 400);
        }

        $adds = $validated['add'] ?? [];
        $removes = $validated['remove'] ?? [];

        // validate adds belong to establishment
        if (!empty($adds)) {
            $validAddIds = Student::whereIn('id', $adds)->where('establishment_id', $establishmentId)->pluck('id')->toArray();
            if (count($validAddIds) !== count($adds)) {
                $invalid = array_diff($adds, $validAddIds);
                return response()->json(['errors' => 'Hibás hozzáadott diák ID: ' . implode(', ', $invalid)], 400);
            }
        }

        if (!empty($removes)) {
            $validRemoveIds = Student::whereIn('id', $removes)->where('establishment_id', $establishmentId)->pluck('id')->toArray();
            if (count($validRemoveIds) !== count($removes)) {
                $invalid = array_diff($removes, $validRemoveIds);
                return response()->json(['errors' => 'Hibás eltávolítandó diák ID: ' . implode(', ', $invalid)], 400);
            }
        }

        DB::transaction(function () use ($adds, $removes, $classId) {
            if (!empty($adds)) {
                $userIds = Student::whereIn('id', $adds)->pluck('user_id')->toArray();

                $already = ClassStudent::where('class_id', $classId)->whereIn('user_id', $userIds)->pluck('user_id')->toArray();
                $toInsert = array_diff($userIds, $already);

                foreach ($toInsert as $userId) {
                    ClassStudent::create(['user_id' => $userId, 'class_id' => $classId]);
                }

                if (!empty($toInsert)) {
                    $class = ClassModel::find($classId);
                    if ($class) {
                        $this->retroactiveEventShow((int) $class->establishment_id, (int) $classId, $toInsert);
                    }
                }
            }

            if (!empty($removes)) {
                $userIdsToRemove = Student::whereIn('id', $removes)->pluck('user_id')->toArray();
                ClassStudent::where('class_id', $classId)->whereIn('user_id', $userIdsToRemove)->delete();
            }
        });

        return response()->json(['message' => 'Osztály tagság frissítve']);
    }

    private function retroactiveEventShow(int $establishmentId, int $classId, array $userIds): void
    {
        $class = ClassModel::find($classId);
        if (!$class) {
            return;
        }

        $normalizedUserIds = collect($userIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values();

        if ($normalizedUserIds->isEmpty()) {
            return;
        }

        $targetedEventIds = Event::query()
            ->where('type', 'local')
            ->where('establishment_id', $establishmentId)
            ->where(function ($query) use ($classId, $class) {
                $query->where('target_group', 'teljes_iskola')
                    ->orWhere(function ($subQuery) use ($classId) {
                            $subQuery->whereIn('target_group', ['osztaly_szintu', 'sajat_osztaly'])
                                ->whereJsonContains('target_class_ids', $classId);
                    })
                    ->orWhere(function ($subQuery) use ($class) {
                        $subQuery->whereIn('target_group', ['evfolyam_szintu', 'evfolyam'])
                            ->whereJsonContains('target_grade_ids', (int) $class->grade);
                    });
            })
            ->pluck('id');

        if ($targetedEventIds->isEmpty()) {
            return;
        }

        foreach ($targetedEventIds as $eventId) {
            foreach ($normalizedUserIds as $userId) {
                EventShown::firstOrCreate([
                    'event_id' => (int) $eventId,
                    'user_id' => (int) $userId,
                    'establishment_id' => (int) $establishmentId,
                ]);
            }
        }
    }
}
