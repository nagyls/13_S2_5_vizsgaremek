<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    // Email cím megerősítése
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Az email cím már meg lett erősítve.'
            ], 200);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json([
            'message' => 'Az email cím sikeresen megerősítve!'
        ], 200);
    }

    //ujraküldés
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Az email cím már meg lett erősítve.'
            ], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Megerősítő email újraküldve!'
        ], 200);
    }

    // Ellenőrzés, hogy az email cím meg lett-e erősítve
    public function check(Request $request)
    {
        return response()->json([
            'verified' => $request->user()->hasVerifiedEmail()
        ], 200);
    }
}
