<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;


class ClassController extends Controller
{

    //Post Class

    public function store(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'grade' => ['required', 'integer'],
            'establishment_id' => ['required', 'exists:establishments,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ], [
            'name.required'             => 'Az osztály neve kötelező.',
            'name.string'               => 'Az osztály neve szöveges érték kell legyen.',
            'name.max'                  => 'Az osztály neve nem lehet hosszabb 100 karakternél.',
            'grade.required'            => 'A évfolyam megadása kötelező.',
            'grade.integer'             => 'A évfolyamnak egész számnak kell lennie.',
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.exists'   => 'Nem létező intézmény.',
        ]);

        $class = ClassModel::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
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
    //Get Classes
    public function getClasses(Request $request, $establishment)
    {
        $user = $request->user();

        $classes = ClassModel::where('establishment_id', $establishment)->orderBy('grade')->orderBy('name')->get();
        return response()->json([
            'data' => $classes
        ]);
    }
}
