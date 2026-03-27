<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;

class VerificationController extends Controller
{
    private function verificationResponse(string $status, $id, $hash)
    {
        $frontendUrl = trim((string) env('FRONTEND_URL', ''));

        if ($frontendUrl !== '') {
            $frontendBaseUrl = rtrim($frontendUrl, '/');
            return redirect()->away("{$frontendBaseUrl}/verify-email/{$id}/{$hash}?status={$status}");
        }

        return response()->view('verification-result', [
            'status' => $status,
        ]);
    }

    // Email cím megerősítése
    public function verify(Request $request, $id, $hash)
    {
        if (!URL::hasValidSignature($request, false)) {
            return $this->verificationResponse('expired', $id, $hash);
        }

        $user = User::find($id);

        if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->verificationResponse('invalid', $id, $hash);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->verificationResponse('already-verified', $id, $hash);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->verificationResponse('success', $id, $hash);
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
