<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;

class ClassController extends Controller
{

    //Post Class

    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'grade' => ['required', 'integer'],
            'establishment_id' => ['required', 'exists:establishments,id'],
        ],[
            'name.required'             => 'Az osztály neve kötelező.',
            'name.string'               => 'Az osztály neve szöveges érték kell legyen.',
            'name.max'                  => 'Az osztály neve nem lehet hosszabb 100 karakternél.',
            'grade.required'            => 'A évfolyam megadása kötelező.',
            'grade.integer'             => 'A évfolyamnak egész számnak kell lennie.',
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.exists'   => 'Nem létező intézmény.',
        ]);

        $class = ClassModel::create([
            'user_id' => $user ? $user->id : null,
            'name' => $request->name,
            'grade' => $request->grade,
            'establishment_id' => $request->establishment_id,
        ]);

        return response()->json([
            'message' => 'Osztály létrehozva!',
            'class' => $class
        ], 201);
    }

}
