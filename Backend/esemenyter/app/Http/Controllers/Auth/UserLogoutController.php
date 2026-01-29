<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;

class UserLogoutController extends Controller
{
    //
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return  response()->json([
            'message' => 'Sikeres kijelentkezés!',
        ]);
    }
}
