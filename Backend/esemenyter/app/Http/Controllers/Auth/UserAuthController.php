<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class UserAuthController extends Controller
{
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

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Hibás jelszó!'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        //intézmény azonosítók lekérése a userhez
        $userTeacher = $user->staffs()->exists();
        $userStudent = $user->students()->exists();



        if ($userTeacher) {
            $StaffEstablishments = $user->staffs()->pluck('establishment_id')->toArray();
        }
        if ($userStudent) {
            $studentEstablishments = $user->student()->pluck('establishment_id')->toArray();
        }
        return response()->json([
            'message' => 'Sikeres bejelentkezés!',
            'user' => $user,
            'token' => $token,
            //'student_establishment_ids' => array_unique($studentEstablishments),
            //'teacher_establishment_ids' => array_unique($StaffEstablishments),
        ]);
    }
    public function storeStudent(Request $request) {}
}
