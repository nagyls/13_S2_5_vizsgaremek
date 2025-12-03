<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Felhasználó lekérése email alapján
        $user = DB::table('users')->where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Hibás email vagy jelszó!'
            ], 401);
        }

        // Jelszó ellenőrzés
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Hibás email vagy jelszó!'
            ], 401);
        }

        return response()->json([
            'message' => 'Sikeres bejelentkezés!',
            'user' => $user
        ]);
    }
}
