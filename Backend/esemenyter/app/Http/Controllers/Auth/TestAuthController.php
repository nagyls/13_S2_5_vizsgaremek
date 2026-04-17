<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TestAuthController extends Controller
{
    public function verifyUser(Request $request)
    {
        // Csak local vagy testing környezetben fusson a biztonság érdekében
        if (!App::environment(['local', 'testing'])) {
            return response()->json(['message' => 'Ez a funkció csak fejlesztői környezetben érhető el.'], 403);
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return response()->json(['message' => 'Email sikeresen verifikálva (teszt mód).']);
        }

        return response()->json(['message' => 'Az email már verifikálva volt vagy a felhasználó nem található.']);
    }
}
