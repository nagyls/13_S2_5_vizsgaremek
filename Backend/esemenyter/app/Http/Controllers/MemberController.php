<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Establishment;
use App\Models\User;
use App\Models\Staff;
use App\Models\Student;
use App\Models\ClassStudent;
use App\Models\ClassModel;
use App\Models\Event;
use App\Models\EventShown;

use Illuminate\Support\Facades\DB;

class MemberController extends Controller
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
            ->select('users.id', 'users.name', 'users.email', 'staffs.alias', 'staffs.created_at', 'staffs.updated_at')
            ->distinct()
            ->get();

        return response()->json([
            'data' => $staff
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
        ], [
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

        $alreadyMember = ClassStudent::where('class_id', $classId)
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
            ->select('users.id', 'students.id as student_id', 'users.name', 'students.alias', 'users.email', 'students.created_at', 'students.updated_at')
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

        $classIdsInEstablishment = ClassModel::where('establishment_id', $establishmentId)
            ->pluck('id')
            ->toArray();

        if (!empty($classIdsInEstablishment)) {
            ClassStudent::whereIn('class_id', $classIdsInEstablishment)
                ->whereIn('user_id', $userIds)
                ->delete();
        }

        if (!empty($userIds)) {
            EventShown::where('establishment_id', $establishmentId)
                ->whereIn('user_id', $userIds)
                ->delete();
        }

        Student::whereIn('id', $validStudentsIds)
            ->where('establishment_id', $establishmentId)
            ->delete();

        $this->changeUsersCurrentEstablishmentAfterRemoval($userIds, $establishmentId);

        return response()->json([
            'message' => 'Diák(ok) eltávolítva az intézményből!'
        ]);
    }

    public function removeStaff(Request $request)
    {
        $authUser = $request->user();

        $validated = $request->validate([
            'establishment_id' => 'required|integer|exists:establishments,id',
            'user_id' => 'required|array|min:1',
            'user_id.*' => 'integer|exists:users,id',
        ], [
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.integer' => 'Az intézmény azonosítónak egész számnak kell lennie.',
            'establishment_id.exists' => 'Intézmény nem található!',
            'user_id.required' => 'Legalább egy felhasználót meg kell adni.',
            'user_id.array' => 'A user_id mezőnek tömbnek kell lennie.',
            'user_id.min' => 'Legalább egy felhasználót meg kell adni.',
            'user_id.*.integer' => 'A user_id elemeinek egész számnak kell lennie.',
            'user_id.*.exists' => 'A megadott felhasználó nem található.',
        ]);

        $establishmentId = (int) $validated['establishment_id'];

        if (!$this->isAdminEstablishment($authUser->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $targetUserIds = collect($validated['user_id'])
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->toArray();

        if (in_array((int) $authUser->id, $targetUserIds, true)) {
            return response()->json([
                'message' => 'Saját felhasználódat nem tudod eltávolítani az intézményből.'
            ], 422);
        }

        $staffRows = Staff::where('establishment_id', $establishmentId)
            ->whereIn('user_id', $targetUserIds)
            ->get(['id', 'user_id', 'role']);

        $foundUserIds = $staffRows
            ->pluck('user_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->toArray();

        if (count($foundUserIds) !== count($targetUserIds)) {
            $invalidIds = array_values(array_diff($targetUserIds, $foundUserIds));
            return response()->json([
                'message' => 'Egy vagy több felhasználó nem tanár ebben az intézményben.',
                'invalid_user_ids' => $invalidIds,
            ], 400);
        }

        $adminUserIds = $staffRows
            ->where('role', 'admin')
            ->pluck('user_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->toArray();

        if (!empty($adminUserIds)) {
            return response()->json([
                'message' => 'Admin jogosultságú tag nem távolítható el ezzel a művelettel.',
                'admin_user_ids' => $adminUserIds,
            ], 422);
        }

        ClassModel::where('establishment_id', $establishmentId)
            ->whereIn('user_id', $targetUserIds)
            ->update(['user_id' => null]);

        Staff::where('establishment_id', $establishmentId)
            ->whereIn('user_id', $targetUserIds)
            ->delete();

        $this->changeUsersCurrentEstablishmentAfterRemoval($targetUserIds, $establishmentId);

        return response()->json([
            'message' => 'Tanár(ok) eltávolítva az intézményből.',
            'removed_user_ids' => $targetUserIds,
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
        if ($this->isMemberEstablishment($memberId, $establishmentId)) {
            return response()->json(['errors' => 'A megadott tag nem tartozik az intézményhez!'], 400);
        }
        if ($this->isStaffEstablishment($memberId, $establishmentId)) {
            $staff = Staff::find($memberId);
            $staff->alias = $validated['alias'];
            $staff->save();
            return response()->json(['message' => 'Álnév frissítve']);
        }
        $student = Student::find($memberId);
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

    private function changeUsersCurrentEstablishmentAfterRemoval(array $userIds, int $removedEstablishmentId): void
    {
        $normalizedUserIds = collect($userIds)
            ->map(function ($id) {
                return $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values();

        if ($normalizedUserIds->isEmpty()) {
            return;
        }

        $usersToSync = User::whereIn('id', $normalizedUserIds->all())
            ->where('establishment_id', $removedEstablishmentId)
            ->get(['id', 'establishment_id']);

        foreach ($usersToSync as $targetUser) {
            $nextStaffEstablishmentId = Staff::where('user_id', $targetUser->id)
                ->where('establishment_id', '!=', $removedEstablishmentId)
                ->orderBy('establishment_id')
                ->value('establishment_id');

            $nextStudentEstablishmentId = Student::where('user_id', $targetUser->id)
                ->where('establishment_id', '!=', $removedEstablishmentId)
                ->orderBy('establishment_id')
                ->value('establishment_id');

            $nextEstablishmentId = $nextStaffEstablishmentId ?? $nextStudentEstablishmentId;

            $targetUser->establishment_id = $nextEstablishmentId ? $nextEstablishmentId : null;
            $targetUser->save();
        }
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
                    'event_id' => $eventId,
                    'user_id' => $userId,
                    'establishment_id' => $establishmentId,
                ]);
            }
        }
    }
}
