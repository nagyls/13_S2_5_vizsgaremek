<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class UserAuthController extends Controller
{
    const AUTH_TOKEN_NAME = 'auth_token';
    //
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'exists:users,email'],
            'password' =>  ['required', 'string'],
        ], [
            'email.exists' => 'Hibás Email!'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Hibás jelszó vagy email!'], 401);
        }
        $token = $user->createToken(self::AUTH_TOKEN_NAME)->plainTextToken;
        return response()->json([
            'message' => 'Sikeres bejelentkezés!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'establishment_id' => $user->establishment_id,
            ],
            'token' => $token,
        ]);
    }
    
}
