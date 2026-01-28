<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Controller;


class AuthController extends Controller
{
    //
     /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()->min(6)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;


        return response()->json([
            'message' => 'Sikeres regisztráció!',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return [
                'message' => 'Invalid credentials'
            ];
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
        
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