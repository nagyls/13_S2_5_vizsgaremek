<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstablishmentRequest;
use App\Models\Personel;

class RequestController extends Controller
{
    //
    private function isAdminEstablishment($userId, $establishmentId)
    {
        return Personel::where('user_id', $userId)
            ->where('establishment_id', $establishmentId)
            ->where('role', 'admin')
            ->exists();
    }
    public function getStudentRequests(Request $request, $establishmentId)
    {
        $user = $request->user();

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $requests = EstablishmentRequest::where('establishment_id', $establishmentId)->where('role', 'student')->get();

        return response()->json([
            'data' => $requests
        ]);
    }
    public function getTeacherRequests(Request $request, $establishmentId)
    {
        $user = $request->user();

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $requests = EstablishmentRequest::where('establishment_id', $establishmentId)->where('role', 'teacher')->get();

        return response()->json([
            'data' => $requests
        ]);
    }
    
    
}
