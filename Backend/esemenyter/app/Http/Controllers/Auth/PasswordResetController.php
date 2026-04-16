<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
        ], [
            'email.required' => 'Az email cim megadasa kotelezo.',
            'email.email' => 'Ervenytelen email cim.',
        ]);

        $status = Password::sendResetLink([
            'email' => strtolower($validated['email']),
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Nem sikerült elküldeni a jelszó-visszaállító emailt.',
            ], 422);
        }

        return response()->json([
            'message' => 'A jelszó-visszaállító email elküldve.',
        ], 200);
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => ['required', 'confirmed', PasswordRule::min(6)->mixedCase()->numbers()],
            'password_confirmation' => 'required|string',
        ], [
            'token.required' => 'A visszaallitasi token hianyzik vagy ervenytelen.',
            'email.required' => 'Az email cim megadasa kotelezo.',
            'email.email' => 'Ervenytelen email cim.',
            'password.required' => 'Az uj jelszo megadasa kotelezo.',
            'password.confirmed' => 'A ket jelszo nem egyezik.',
            'password_confirmation.required' => 'Az uj jelszo megerositese kotelezo.',
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();
        if ($user && Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Az uj jelszo nem egyezhet meg a jelenlegi jelszoval.',
            ], 422);
        }

        $status = Password::reset(
            [
                'email' => strtolower($validated['email']),
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation', ''),
                'token' => $validated['token'],
            ],
            function ($user) use ($validated) {
                $user->forceFill([
                    'password' => Hash::make($validated['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'A jelszó-visszaállítás sikertelen vagy a link lejárt.',
            ], 422);
        }

        return response()->json([
            'message' => 'A jelszó sikeresen módosítva.',
        ], 200);
    }
}