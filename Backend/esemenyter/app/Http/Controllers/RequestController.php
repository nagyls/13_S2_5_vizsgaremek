<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstablishmentRequest;
use App\Models\Personel;
use App\Models\Establishment;
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
        $isPartofPersonel = Personel::where('user_id', $user->id)->where('establishment_id', $request->establishment_id)->first();
        $isPartofStudents = Student::where('user_id', $user->id)->where('establishment_id', $request->establishment_id)->first();

        if ($isRequest) {
            return response()->json(['message' => 'Kérelem már létezik!'], 409);
        }
        if ($isPartofPersonel || $isPartofStudents) {
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

}
