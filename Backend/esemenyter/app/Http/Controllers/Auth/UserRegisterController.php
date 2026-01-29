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

    
}