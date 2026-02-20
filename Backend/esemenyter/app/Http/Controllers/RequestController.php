<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstablishmentRequest;
use App\Models\Staff;
use App\Models\Student;

class RequestController extends Controller
{
    // Get requestek

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

    ####### Post requestek
    public function submitRequest(Request $request)
    {
        $user = $request->user();

        $isRequest = EstablishmentRequest::where('user_id', $user->id)->where('establishment_id', $request->establishment_id)->first();
        $isPartofStaff = Staff::where('user_id', $user->id)->where('establishment_id', $request->establishment_id)->first();
        $isPartofStudents = Student::where('user_id', $user->id)->where('establishment_id', $request->establishment_id)->first();

        if ($isRequest) {
            return response()->json(['message' => 'Kérelem már létezik!'], 409);
        }
        if ($isPartofStaff || $isPartofStudents) {
            return response()->json(['message' => 'Már tagja vagy az intézménynek!'], 409);
        }

        $request->validate([
            'establishment_id' => ['required', 'integer', 'exists:establishments,id'],
            'role' => ['required', 'string', 'in:student,teacher'],
        ], [
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.exists'   => 'Nem létező intézmény.',
            'role.in'                   => 'A szerepnek "student" vagy "teacher" kell legyen.',
        ]);

        $newRequest = EstablishmentRequest::create([
            'user_id' => $user->id,
            'establishment_id' => $request->establishment_id,
            'role' => $request->role,
        ]);
        return response()->json(['message' => 'Kérelem benyújtva!'], 201);
    }
    //elfogadás, elutasítás
    public function handleRequest(Request $request, $requestId)
    {
        $user = $request->user();
        $establishmentRequest = EstablishmentRequest::find($requestId);
        if (!$establishmentRequest) {
            return response()->json(['message' => 'Kérelem nem található!'], 404);
        }
        if (!$this->isAdminEstablishment($user->id, $establishmentRequest->establishment_id)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $request->validate([
            'action' => ['required', 'string', 'in:accept,reject'],
        ], [
            'action.in' => 'Az action mezőben "accept" vagy "reject" értéknek kell lennie.',
            'action.required' => 'Az action mező megadása kötelező.',
        ]);

        if ($request->action === 'accept') {
            if ($establishmentRequest->role === 'teacher') {
                Staff::create([
                    'user_id' => $establishmentRequest->user_id,
                    'establishment_id' => $establishmentRequest->establishment_id,
                    'role' => 'teacher',
                ]);
            } else {
                Student::create([
                    'user_id' => $establishmentRequest->user_id,
                    'establishment_id' => $establishmentRequest->establishment_id,
                ]);
            }
            $establishmentRequest->delete();
            return response()->json(['message' => 'Kérelem elfogadva!']);
        } else {
            $establishmentRequest->delete();
            return response()->json(['message' => 'Kérelem elutasítva!']);
        }
    }
    // visszavon
    public function revokeRequest(Request $request, $establishmentId)
    {
        $user = $request->user();
        $establishmentRequest = EstablishmentRequest::where('user_id', $user->id)->where('establishment_id', $establishmentId)->first();
        if (!$establishmentRequest) {
            return response()->json(['message' => 'Kérelem nem található!'], 404);
        }
        $establishmentRequest->delete();
        return response()->json(['message' => 'Kérelem visszavonva!']);
    }
}
