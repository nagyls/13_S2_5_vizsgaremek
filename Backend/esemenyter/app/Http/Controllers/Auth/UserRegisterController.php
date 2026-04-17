<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;


class UserRegisterController extends Controller
{

    public function register(Request $request)
    {
        $request->merge(['email' => strtolower($request->email)]);

        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::min(6)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        // verification küldés
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Sikeres regisztráció! Kérjük, ellenőrizze az email fiókját a megerősítő linkért.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'email_verified' => false
        ], 201);
    }

    
}