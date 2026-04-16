<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Staff;
use App\Models\Establishment;


class ClassController extends Controller
{

    //osztály létrehozása

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:100',
            'grade' => 'required|integer',
            'capacity' => 'nullable|integer',
            'establishment_id' => 'required|exists:establishments,id',
            'user_id' => 'nullable|integer|exists:users,id',
        ], [
            'capacity.integer'          => 'A maximális létszámnak egész számnak kell lennie.',
            'name.required'             => 'Az osztály neve kötelező.',
            'name.string'               => 'Az osztály neve szöveges érték kell legyen.',
            'name.max'                  => 'Az osztály neve nem lehet hosszabb 100 karakternél.',
            'grade.required'            => 'A évfolyam megadása kötelező.',
            'grade.integer'             => 'A évfolyamnak egész számnak kell lennie.',
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.exists'   => 'Nem létező intézmény.',
            'user_id.integer'          => 'A tanár azonosítójának egész számnak kell lennie.',
            'user_id.exists'           => 'Nem létező tanár.',
        ]);

        $class = ClassModel::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'capacity' => $request->capacity,
            'grade' => $request->grade,
            'establishment_id' => $request->establishment_id,
        ]);
        $user = $request->user();
        if (!$this->isAdminEstablishment($user->id, $request->establishment_id)) {
            $class->user_id = $user->id;
            $class->save();
        }
        return response()->json([
            'message' => 'Osztály létrehozva!',
            'class' => $class
        ], 201);
    }
    //Osztályok lekérdezése
    public function getClasses(Request $request, $establishment)
    {
        $user = $request->user();
        if (!$this->isStaffEstablishment($user->id, $establishment)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }
        $classes = ClassModel::where('establishment_id', $establishment)
            ->orderBy('grade')
            ->orderBy('name')
            ->get();

        $studentCounts = User::join('class_students', 'users.id', '=', 'class_students.user_id')
            ->whereIn('class_students.class_id', $classes->pluck('id'))
            ->selectRaw('class_students.class_id, COUNT(users.id) as student_count')
            ->groupBy('class_students.class_id')
            ->pluck('student_count', 'class_students.class_id');

        return response()->json([
            'data' => $classes->map(function ($item) use ($studentCounts) {
                $teacherUser = User::find($item->user_id);
                $teacherAlias = null;

                if ($item->user_id) {
                    $teacherAlias = Staff::where('user_id', $item->user_id)
                        ->where('establishment_id', $item->establishment_id)
                        ->value('alias');
                }

                $teacherName = $teacherAlias ?: optional($teacherUser)->name;
                $studentCount = 0;
                if (isset($studentCounts[$item->id])) {
                    $studentCount = (int) $studentCounts[$item->id];
                }

                return [
                    'id' => $item->id,
                    'user' => $teacherName,
                    'teacher_name' => $teacherName,
                    'user_id' => $item->user_id,
                    'name' => $item->name,
                    'grade' => $item->grade,
                    'capacity' => $item->capacity,
                    'student_count' => $studentCount,
                ];
            })->values(),
        ]);
    }
    //Osztály tagok lekérdezése
    public function getClassMembers(Request $request, $establishmentId, $classId)
    {
        $user = $request->user();
        if (!$this->isMemberEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }
        $establishment = Establishment::find($establishmentId);
        $class = ClassModel::find($classId);
        if (!$establishment || !$class) {
            return response()->json(['message' => 'Intézmény vagy osztály nem található!'], 404);
        }
        if ($class->establishment_id != $establishment->id) {
            return response()->json(['message' => 'Az osztály nem tartozik az intézményhez!'], 400);
        }

        $students = User::join('class_students', 'users.id', '=', 'class_students.user_id')
            ->leftJoin('students', 'users.id', '=', 'students.user_id')
            ->where('class_students.class_id', $classId)
            ->select('users.*', 'students.alias')
            ->get();

        return response()->json([
            'data' => $students
        ]);
    }
    public function getClassMemmbersInMass(Request $request, $establishmentId)
    {
        $user = $request->user();
        if (!$this->isStaffEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }
        $validated = validator($request->all(), [
            'class_ids' => 'required|array',
            'class_ids.*' => 'integer|exists:classes,id',
        ], [
            'class_ids.required' => 'A class_ids mező kötelező.',
            'class_ids.array' => 'A class_ids mezőnek tömbnek kell lennie.',
            'class_ids.*.integer' => 'A class_ids tömb elemeinek egész számnak kell lennie.',
            'class_ids.*.exists' => 'A class_ids tömb elemeinek léteznie kell a classes táblában.',
        ])->validate();

        //  A megadott osztályok az adott intézményhez tartoznak-e
        $classesInEstablishment = ClassModel::whereIn('id', $validated['class_ids'])
            ->where('establishment_id', $establishmentId)
            ->pluck('id')
            ->toArray();

        $invalidIds = array_values(array_diff($validated['class_ids'], $classesInEstablishment));
        if (!empty($invalidIds)) {
            return response()->json([
                'message' => 'Egy vagy több osztály nem tartozik az intézményhez!',
                'invalid_class_ids' => $invalidIds
            ], 400);
        }

        $studentIds = User::join('class_students', 'users.id', '=', 'class_students.user_id')
            ->whereIn('class_students.class_id', $classesInEstablishment)
            ->distinct()
            ->pluck('users.id');

        $teacherIds = ClassModel::whereIn('id', $classesInEstablishment)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $userIds = $studentIds
            ->concat($teacherIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values();

        return response()->json([
            'student_ids' => $studentIds->map(function ($id) {
                return (int) $id;
            })->values(),
            'teacher_ids' => $teacherIds->map(function ($id) {
                return (int) $id;
            })->values(),
            'user_ids' => $userIds,
            'class_ids' => array_values($classesInEstablishment),
        ]);
    }
    public function getEstablishmentGrades(Request $request, $establishmentId)
    {
        $user = $request->user();
        if (!$this->isStaffEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $grades = ClassModel::where('establishment_id', $establishmentId)
            ->selectRaw('grade, COUNT(*) as class_count')
            ->groupBy('grade')
            ->orderBy('grade')
            ->get()
            ->map(function ($item) {
                return [
                    'grade' => (int) $item->grade,
                    'class_count' => (int) $item->class_count,
                ];
            })
            ->values();

        return response()->json([
            'data' => $grades,
        ]);
    }

    public function getGradeMembersInMass(Request $request, $establishmentId)
    {
        $user = $request->user();
        if (!$this->isStaffEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $validated = validator($request->all(), [
            'grade_ids' => 'required|array|min:1',
            'grade_ids.*' => 'integer|min:1',
        ], [
            'grade_ids.required' => 'A grade_ids mező kötelező.',
            'grade_ids.array' => 'A grade_ids mezőnek tömbnek kell lennie.',
            'grade_ids.min' => 'Legalább egy évfolyamot meg kell adni.',
            'grade_ids.*.integer' => 'A grade_ids tömb elemeinek egész számnak kell lennie.',
            'grade_ids.*.min' => 'Az évfolyam számának pozitív egésznek kell lennie.',
        ])->validate();

        $classes = ClassModel::where('establishment_id', $establishmentId)
            ->whereIn('grade', $validated['grade_ids'])
            ->get(['id', 'grade', 'user_id']);

        $resolvedGrades = $classes
            ->pluck('grade')
            ->map(function ($grade) {
                return (int) $grade;
            })
            ->unique()
            ->values();

        $missingGrades = collect($validated['grade_ids'])
            ->map(function ($grade) {
                return (int) $grade;
            })
            ->diff($resolvedGrades)
            ->values();

        if ($missingGrades->isNotEmpty()) {
            return response()->json([
                'message' => 'Egy vagy több évfolyam nem található az intézményben!',
                'invalid_grade_ids' => $missingGrades,
            ], 400);
        }

        $classIds = $classes
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values();

        $studentIds = User::join('class_students', 'users.id', '=', 'class_students.user_id')
            ->whereIn('class_students.class_id', $classIds)
            ->distinct()
            ->pluck('users.id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values();

        $teacherIds = $classes
            ->pluck('user_id')
            ->filter(function ($id) {
                return !is_null($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values();

        $userIds = $studentIds
            ->concat($teacherIds)
            ->unique()
            ->values();

        return response()->json([
            'grade_ids' => $resolvedGrades,
            'class_ids' => $classIds,
            'student_ids' => $studentIds,
            'teacher_ids' => $teacherIds,
            'user_ids' => $userIds,
        ]);
    }
    public function updateClassTeacher(Request $request, $establishmentId, $classId)
    {
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

        if ($class->establishment_id != $establishment->id) {
            return response()->json([
                'message' => 'Az osztály nem tartozik az intézményhez!'
            ], 400);
        }

        $user = $request->user();

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $validated = validator($request->all(), [
            'teacher_id' => 'sometimes|nullable|integer|exists:users,id',
            'name' => 'sometimes|required|string|max:100|alpha',
            'grade' => 'sometimes|required|integer|min:1|max:100',
            'capacity' => 'sometimes|required|integer|min:1|max:200',
        ], [
            'teacher_id.integer' => 'A teacher_id értéknek egész számnak kell lennie.',
            'teacher_id.exists' => 'A teacher_id értéknek léteznie kell a users táblában.',
            'name.required' => 'Az osztály neve kötelező.',
            'name.string' => 'Az osztály neve szöveges érték kell legyen.',
            'name.max' => 'Az osztály neve nem lehet hosszabb 100 karakternél.',
            'name.alpha' => 'Az osztály neve csak betűket tartalmazhat.',
            'grade.required' => 'Az évfolyam megadása kötelező.',
            'grade.integer' => 'Az évfolyamnak egész számnak kell lennie.',
            'grade.min' => 'Az évfolyamnak legalább 1-nek kell lennie.',
            'grade.max' => 'Az évfolyam nem lehet nagyobb 100-nál.',
            'capacity.required' => 'A maximális létszám megadása kötelező.',
            'capacity.integer' => 'A maximális létszámnak egész számnak kell lennie.',
            'capacity.min' => 'A maximális létszámnak legalább 1-nek kell lennie.',
            'capacity.max' => 'A maximális létszám nem lehet nagyobb 200-nál.',
        ])->validate();

        if (empty($validated)) {
            return response()->json([
                'message' => 'Nincs frissíthető mező a kérésben.'
            ], 422);
        }

        if (array_key_exists('teacher_id', $validated) && !is_null($validated['teacher_id']) && !$this->isStaffEstablishment($validated['teacher_id'], $establishmentId)) {
            return response()->json([
                'message' => 'A megadott tanár nem tagja az intézménynek!'
            ], 400);
        }

        if (array_key_exists('teacher_id', $validated)) {
            $class->user_id = $validated['teacher_id'];
        }

        if (array_key_exists('name', $validated)) {
            $class->name = trim($validated['name']);
        }

        if (array_key_exists('grade', $validated)) {
            $class->grade = $validated['grade'];
        }

        if (array_key_exists('capacity', $validated)) {
            $class->capacity = $validated['capacity'];
        }

        $class->save();

        return response()->json([
            'message' => 'Osztály sikeresen frissítve!'
        ]);
    }
    //Osztály törlése
    public function deleteClass(Request $request, $establishmentId, $classId)
    {
        $user = $request->user();
        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }
        $class = ClassModel::find($classId);
        if (!$class) {
            return response()->json(['message' => 'Osztály nem található!'], 404);
        }
        if ($class->establishment_id != $establishmentId) {
            return response()->json(['message' => 'Az osztály nem tartozik az intézményhez!'], 400);
        }
        $class->delete();
        return response()->json(['message' => 'Osztály sikeresen törölve!']);
    }
}
