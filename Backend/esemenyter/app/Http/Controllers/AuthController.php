<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
       $request->validate([
        'username' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6'
        ]);

        User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Sikeres regisztráció!']);
    }
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        // Felhasználó lekérése username alapján
        $user = User::where('name', $request->username)->first();
        if (!$user) {
            return response()->json(['message' => 'Hibás felhasználónév!'], 401);
        }

        // Jelszó ellenőrzés
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Hibás jelszó!'], 401);
        }

        return response()->json([
            'message' => 'Sikeres bejelentkezés!',
            'user' => $user
        ]);
    }
}