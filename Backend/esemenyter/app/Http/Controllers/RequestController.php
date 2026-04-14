<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Establishment;
use App\Models\EstablishmentRequest;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;

class RequestController extends Controller
{
    public function getMyPendingRequest(Request $request)
    {
        $user = $request->user();

        $pendingRequest = EstablishmentRequest::where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (!$pendingRequest) {
            return response()->json([
                'pending' => false,
                'request' => null,
            ]);
        }

        return response()->json([
            'pending' => true,
            'request' => [
                'id' => $pendingRequest->id,
                'establishment_id' => $pendingRequest->establishment_id,
                'role' => $pendingRequest->role,
                    'status' => $pendingRequest->status,
                'created_at' => $pendingRequest->created_at,
            ],
        ]);
    }

    public function getMyRequestStatus(Request $request, $establishmentId)
    {
        $user = $request->user();

        $hasStaffMembership = Staff::where('user_id', $user->id)
            ->where('establishment_id', $establishmentId)
            ->exists();

        $hasStudentMembership = Student::where('user_id', $user->id)
            ->where('establishment_id', $establishmentId)
            ->exists();

        if ($hasStaffMembership || $hasStudentMembership) {
            return response()->json([
                'status' => 'accepted',
                'pending' => false,
            ]);
        }

        $pendingRequest = EstablishmentRequest::where('user_id', $user->id)
            ->where('establishment_id', $establishmentId)
            ->latest('id')
            ->first();

        if ($pendingRequest) {
            return response()->json([
                'status' => $pendingRequest->status,
                'pending' => $pendingRequest->status === 'pending',
                'role' => $pendingRequest->role,
                'request_id' => $pendingRequest->id,
            ]);
        }

        return response()->json([
            'status' => 'none',
            'pending' => false,
        ]);
    }

    // Get requestek

    public function getStudentRequests(Request $request, $establishmentId)
    {
        $user = $request->user();
        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $requests = EstablishmentRequest::where('establishment_id', $establishmentId)->where('role', 'student')->get();
        $data = $requests->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'role' => $item->role,
                'alias' => $item->alias,
                'created_at' => $item->created_at,
                'name' => $item->userFromId->name,
                'email' => $item->userFromId->email,
            ];
        });
        return response()->json([
            'data' => $data
        ]);
    }
    public function getTeacherRequests(Request $request, $establishmentId)
    {
        $user = $request->user();

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $requests = EstablishmentRequest::where('establishment_id', $establishmentId)->where('role', 'teacher')->get();
        $data = $requests->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'role' => $item->role,
                'alias' => $item->alias,
                'created_at' => $item->created_at,
                'name' => $item->userFromId->name,
                'email' => $item->userFromId->email,
            ];
        });
        return response()->json([
            'data' => $data
        ]);
    }

    ####### Post requestek
    public function submitRequest(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'establishment_id' => ['required', 'integer', 'exists:establishments,id'],
            'role' => ['required', 'string', 'in:student,teacher'],
            'alias' => ['nullable', 'string', 'max:255'],
        ], [
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.exists'   => 'Nem létező intézmény.',
            'role.in'                   => 'A szerepnek "student" vagy "teacher" kell legyen.',
            'alias.string'              => 'Az alias mezőnek szöveges értéknek kell lennie.',
            'alias.max'                 => 'Az alias mező legfeljebb 255 karakter lehet.',
        ]);

        $establishmentId = $validated['establishment_id'];
        $alias = trim((string) ($validated['alias'] ?? ''));
        if ($alias === '') {
            $alias = (string) $user->name;
        }

        $rejectedRequests = EstablishmentRequest::where('user_id', $user->id)
            ->where('establishment_id', $establishmentId)
            ->where('status', 'rejected')
            ->get();

        if ($rejectedRequests->isNotEmpty()) {
            EstablishmentRequest::whereIn('id', $rejectedRequests->pluck('id')->all())->delete();
        }

        $isRequest = EstablishmentRequest::where('user_id', $user->id)
            ->where('establishment_id', $establishmentId)
            ->where('status', 'pending')
            ->first();
        $isPartofStaff = Staff::where('user_id', $user->id)->where('establishment_id', $establishmentId)->first();
        $isPartofStudents = Student::where('user_id', $user->id)->where('establishment_id', $establishmentId)->first();

        if ($isRequest) {
            return response()->json(['message' => 'Kérelem már létezik!'], 409);
        }
        if ($isPartofStaff || $isPartofStudents) {
            return response()->json(['message' => 'Már tagja vagy az intézménynek!'], 409);
        }

        $establishment = Establishment::find($establishmentId);
        if (!$establishment || !$establishment->accepts_join_requests) {
            return response()->json(['message' => 'Az intézmény jelenleg nem fogad új csatlakozási kérelmeket.'], 422);
        }

        EstablishmentRequest::create([
            'user_id' => $user->id,
            'establishment_id' => $establishmentId,
            'role' => $validated['role'],
            'alias' => $alias,
            'status' => 'pending',
        ]);
        return response()->json(['message' => 'Kérelem benyújtva!'], 201);
    }
    //elfogadás, elutasítás
    public function handleRequest(Request $request)
    {
        $validated = $request->validate([
            'establishment_id' => ['required', 'integer', 'exists:establishments,id'],
            'action' => ['required', 'string', 'in:accept,reject'],
            'request_id' => ['required', 'array'],
            'request_id.*' => ['integer', 'exists:establishment_requests,id'],
        ], [
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.exists'   => 'Nem létező intézmény.',
            'action.in' => 'Az action mezőben "accept" vagy "reject" értéknek kell lennie.',
            'action.required' => 'Az action mező megadása kötelező.',
            'request_id.required' => 'A request_id mező megadása kötelező.',
            'request_id.array' => 'A request_id mezőnek tömbnek kell lennie.',
            'request_id.*.integer' => 'Minden request_id értéknek egész számnak kell lennie.',
            'request_id.*.exists' => 'Minden request_id értéknek léteznie kell az establishment_requests táblában.'
        ]);

        $user = $request->user();
        if (!$this->isAdminEstablishment($user->id, $validated['establishment_id'])) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        // csak az adott intézményhez tartozó kéréseket dolgozzuk fel
        $requests = EstablishmentRequest::whereIn('id', $validated['request_id'])
            ->where('establishment_id', $validated['establishment_id'])
            ->where('status', 'pending')
            ->get();



        $accepted = 0;
        $rejected = 0;

        if ($requests->isNotEmpty()) {
            if ($validated['action'] === 'accept') {
                foreach ($requests as $item) {
                    $requestAlias = trim((string) $item->alias);
                    if ($requestAlias === '') {
                        $userName = User::where('id', $item->user_id)->value('name');
                        $requestAlias = trim((string) $userName);
                    }

                    if ($item->role === 'teacher') {
                        $staff = Staff::firstOrCreate(
                            ['user_id' => $item->user_id, 'establishment_id' => $item->establishment_id],
                            ['role' => 'teacher', 'alias' => $requestAlias]
                        );

                        if (empty($staff->alias)) {
                            $staff->alias = $requestAlias;
                            $staff->save();
                        }

                        if ($staff->wasRecentlyCreated) {
                            $accepted++;
                            User::where('id', $item->user_id)->update(['establishment_id' => $item->establishment_id]);
                        }
                    } else {
                        $student = Student::firstOrCreate(
                            [
                                'user_id' => $item->user_id,
                                'establishment_id' => $item->establishment_id,
                            ],
                            ['alias' => $requestAlias]
                        );

                        if (empty($student->alias)) {
                            $student->alias = $requestAlias;
                            $student->save();
                        }

                        if ($student->wasRecentlyCreated) {
                            $accepted++;
                            User::where('id', $item->user_id)->update(['establishment_id' => $item->establishment_id]);
                        }
                    }
                    // a kérelem törlése minden feldolgozott esetben
                    $item->delete();
                }
            } else { // reject
                EstablishmentRequest::whereIn('id', $requests->pluck('id')->toArray())
                    ->update(['status' => 'rejected']);
                $rejected = count($requests);
            }
        }

        if ($validated['action'] === 'accept') {
            return response()->json([
                'message' => 'Kérelmek feldolgozva.',
                'accepted' => $accepted,
            ]);
        } else {
            return response()->json([
                'message' => 'Kérelmek feldolgozva.',
                'rejected' => $rejected,
            ]);
        }
    }

    // visszavon
    public function revokeRequest(Request $request, $establishmentId)
    {
        $user = $request->user();
        $establishmentRequest = EstablishmentRequest::where('user_id', $user->id)
            ->where('establishment_id', $establishmentId)
            ->where('status', 'pending')
            ->first();
        if (!$establishmentRequest) {
            return response()->json(['message' => 'Kérelem nem található!'], 404);
        }
        $establishmentRequest->delete();
        return response()->json(['message' => 'Kérelem visszavonva!']);
    }
}
