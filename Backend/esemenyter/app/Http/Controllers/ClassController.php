<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Establishment;


class ClassController extends Controller
{

    //osztály létrehozása

    public function store(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'grade' => ['required', 'integer'],
            'capacity' => ['nullable', 'integer'],
            'establishment_id' => ['required', 'exists:establishments,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
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
        $classes = ClassModel::where('establishment_id', $establishment)->orderBy('grade')->orderBy('name')->get();
        return response()->json([
            'data' => $classes->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user' => optional(User::find($item->user_id))->name,
                    'user_id' => $item->user_id,
                    'name' => $item->name,
                    'grade' => $item->grade,
                    'capacity' => $item->capacity,
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
}
