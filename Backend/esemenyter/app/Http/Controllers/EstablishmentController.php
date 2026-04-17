<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Establishment;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Region;
use App\Models\InnerRegion;
use App\Models\Settlement;

class EstablishmentController extends Controller
{
    // Új intézmény létrehozása és az alapító admin hozzáadása
    public function store(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:establishments,title',
            'description' => 'nullable|string',
            'settlement_id' => 'required|exists:settlements,id',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:32',
            'address' => 'nullable|string|max:255',
            'admin_alias' => 'nullable|string|max:255',
        ], [
            'title.required' => 'Az intézmény neve kötelező.',
            'title.string' => 'Az intézmény neve szöveges érték kell legyen.',
            'title.max' => 'Az intézmény neve nem lehet hosszabb 255 karakternél.',
            'title.unique' => 'Már létezik ilyen nevű intézmény.',
            'description.string' => 'Az intézmény leírása szöveges érték kell legyen.',
            'settlement_id.required' => 'A település azonosító megadása kötelező.',
            'settlement_id.exists' => 'Nem létező település.',
            'website.url' => 'A weboldal URL formátumú kell legyen.',
            'website.max' => 'A weboldal nem lehet hosszabb 255 karakternél.',
            'email.email' => 'Az email cím formátuma érvénytelen.',
            'email.max' => 'Az email cím nem lehet hosszabb 255 karakternél.',
            'phone.string' => 'A telefonszám szöveges érték kell legyen.',
            'phone.max' => 'A telefonszám nem lehet hosszabb 32 karakternél.',
            'address.string' => 'A cím szöveges érték kell legyen.',
            'address.max' => 'A cím nem lehet hosszabb 255 karakternél.',
            'admin_alias.string' => 'Az alias szöveges érték kell legyen.',
            'admin_alias.max' => 'Az alias nem lehet hosszabb 255 karakternél.',
        ]);
        // A létrehozó lesz az intézmény tulajdonosa.
        $validated['user_id'] = $request->user()->id;
        $validated['accepts_join_requests'] = true;
        $adminAliasSource = '';
        if (array_key_exists('admin_alias', $validated)) {
            $adminAliasSource = $validated['admin_alias'];
        }
        $adminAlias = trim((string) $adminAliasSource);
        if ($adminAlias === '') {
            $adminAlias = (string) $request->user()->name;
        }

        // Az alias nem az intézményhez, hanem a staff rekordhoz tartozik.
        unset($validated['admin_alias']);
        $establishment = Establishment::create($validated);

        $staff = Staff::create([
            'role' => 'admin',
            'alias' => $adminAlias,
            'establishment_id' => $establishment->id,
            'user_id' => $request->user()->id,
        ]);

        User::where('id', $request->user()->id)->update(['establishment_id' => $establishment->id]);

        return response()->json([
            'message' => 'Intézmény regisztrálva!',
            'data' => $establishment
        ], 201);
    }


    // Intézmények keresése szűrők alapján 
    public function getEstablishments(Request $request)
    {
        $includeAll = $request->boolean('include_all');

        if ($request->has('region_id') && !empty($request->region_id)) {
            $regionId = (int) $request->region_id;

            $query = Establishment::query()
                ->join('settlements', 'settlements.id', '=', 'establishments.settlement_id')
                ->join('inner_regions', 'inner_regions.id', '=', 'settlements.inner_region_id')
                ->where('inner_regions.region_id', $regionId)
                ->select('establishments.id', 'establishments.title', 'establishments.accepts_join_requests')
                ->distinct();

            if (!$includeAll) {
                $query->where('establishments.accepts_join_requests', true);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('establishments.title', 'LIKE', "%{$search}%");
            }

            $establishments = $query->orderBy('establishments.title')->get();

            return response()->json([
                'data' => $establishments->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'accepts_join_requests' => (bool) $item->accepts_join_requests,
                    ];
                })->values(),
            ]);
        }

        $query = Establishment::query();
        if (!$includeAll) {
            $query->where('accepts_join_requests', true);
        }
        if ($request->has('search') && !empty($request->search) && $request->has('settlement_id') && !empty($request->settlement_id)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%")->where('settlement_id', '=', "{$request->settlement_id}");
        } else if ($request->has('settlement_id') && !empty($request->settlement_id)) {

            $query->where('settlement_id', '=', "{$request->settlement_id}");
            $establishment = $query->orderBy('title')->get();

            return response()->json([
                'data' => $establishment->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'accepts_join_requests' => (bool) $item->accepts_join_requests,
                    ];
                })->values(),
            ]);
        } else {
            return response()->json([
                'data' => []
            ]);
        }

        $establishment = $query->orderBy('title')->get();
        return response()->json([
            'data' => $establishment->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'accepts_join_requests' => (bool) $item->accepts_join_requests,
                ];
            })->values(),
        ]);
    }
    // Egy intézmény részletes adatainak lekérése
    public function getEstablishmentbyId($id)
    {
        $establishment = Establishment::where('establishments.id', $id)->join('settlements', 'settlements.id', '=', 'establishments.settlement_id')
            ->join('inner_regions', 'inner_regions.id', '=', 'settlements.inner_region_id')
            ->join('regions', 'regions.id', '=', 'inner_regions.region_id')
            ->select(
                'establishments.id',
                'establishments.title',
                'establishments.description',
                'establishments.website',
                'establishments.email',
                'establishments.phone',
                'establishments.address',
                'establishments.user_id',
                'settlements.title as settlement_name',
                'inner_regions.title as inner_region_name',
                'regions.id as region_id',
                'regions.title as region_name'
            )
            ->first();

        return response()->json([
            'data' => $establishment
        ]);
    }
    // A bejelentkezett felhasználó összes intézménybeli tagságának listázása
    public function getMyEstablishments(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }

        $staffMemberships = Establishment::query()
            ->join('staffs', 'staffs.establishment_id', '=', 'establishments.id')
            ->where('staffs.user_id', $user->id)
            ->select(
                'establishments.id',
                'establishments.title',
                'establishments.address',
                'establishments.email',
                'establishments.phone',
                'establishments.website',
                'staffs.role as membership_role',
                'staffs.alias as membership_alias'
            )
            ->get();

        $studentMemberships = Establishment::query()
            ->join('students', 'students.establishment_id', '=', 'establishments.id')
            ->where('students.user_id', $user->id)
            ->select(
                'establishments.id',
                'establishments.title',
                'establishments.address',
                'establishments.email',
                'establishments.phone',
                'establishments.website',
                DB::raw("'student' as membership_role"),
                'students.alias as membership_alias'
            )
            ->get();

        $establishments = $staffMemberships
            ->concat($studentMemberships)
            ->unique('id')
            ->sortBy(function ($item) {
                return mb_strtolower($item->title);
            })
            ->values();

        return response()->json([
            'data' => $establishments->map(function ($item) use ($user) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'address' => $item->address,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'website' => $item->website,
                    'role' => $item->membership_role,
                    'alias' => $item->membership_alias,
                    'is_current' => $user->establishment_id === $item->id,
                ];
            })->values()
        ]);
    }

    // Aktív intézmény váltása a felhasználónak
    public function switchEstablishment(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }

        $validated = $request->validate([
            'establishment_id' => 'required|integer|exists:establishments,id',
        ], [
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.integer' => 'Az intézmény azonosítónak egész számnak kell lennie.',
            'establishment_id.exists' => 'Nem létező intézmény.',
        ]);

        $establishmentId = $validated['establishment_id'];

        if (!$this->isMemberEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem vagy tagja ennek az intézménynek.'], 403);
        }

        $establishment = Establishment::find($establishmentId);
        $role = $this->resolveRoleForEstablishment($user->id, $establishmentId);

        $user->establishment_id = $establishmentId;
        $user->save();

        return response()->json([
            'message' => 'Aktív intézmény sikeresen módosítva.',
            'data' => [
                'id' => $establishment->id,
                'title' => $establishment->title,
                'address' => $establishment->address,
                'email' => $establishment->email,
                'phone' => $establishment->phone,
                'website' => $establishment->website,
                'role' => $role,
                'establishment_id' => $establishment->id,
            ]
        ]);
    }

    // Meghatározza a felhasználó szerepét az intézményben
    protected function resolveRoleForEstablishment(int $userId, int $establishmentId): ?string
    {
        if ($this->isAdminEstablishment($userId, $establishmentId)) {
            return 'admin';
        }

        if ($this->isStaffEstablishment($userId, $establishmentId)) {
            return 'teacher';
        }

        if ($this->isMemberEstablishment($userId, $establishmentId)) {
            return 'student';
        }

        return null;
    }

    // A bejelentkezett felhasználó szerepének lekérdezése az adott intézményben
    public function getRole(Request $request, $establishmentId)
    {
        $user = $request->user();

        $role = $this->resolveRoleForEstablishment($user->id, $establishmentId);

        if (!$role) {
            return response()->json(['message' => 'Nem tagja egy intézménynek sem!'], 403);
        }

        return response()->json(['role' => $role]);
    }

    // Csatlakozási kérelmek fogadásának aktuális állapota
    public function getJoinRequestAvailability(Request $request, int $establishmentId)
    {
        $user = $request->user();

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json(['message' => 'Intézmény nem található!'], 404);
        }

        return response()->json([
            'establishment_id' => $establishment->id,
            'accepts_join_requests' => (bool) $establishment->accepts_join_requests,
        ]);
    }

    // Csatlakozási kérelmek fogadásának be- vagy kikapcsolása
    public function updateJoinRequestAvailability(Request $request, int $establishmentId)
    {
        $user = $request->user();

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'Nem Felhatalmazott!'], 403);
        }

        $validated = $request->validate([
            'accepts_join_requests' => 'required|boolean',
        ], [
            'accepts_join_requests.required' => 'A csatlakozási kérelmek fogadása mező kötelező.',
            'accepts_join_requests.boolean' => 'A csatlakozási kérelmek fogadása mező csak igaz/hamis érték lehet.',
        ]);

        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json(['message' => 'Intézmény nem található!'], 404);
        }

        $establishment->accepts_join_requests = $validated['accepts_join_requests'];
        $establishment->save();

        $message = $establishment->accepts_join_requests
            ? 'Az intézmény ismét fogad új csatlakozási kérelmeket.'
            : 'Az intézmény jelenleg nem fogad új csatlakozási kérelmeket.';

        return response()->json([
            'message' => $message,
            'establishment_id' => $establishment->id,
            'accepts_join_requests' => (bool) $establishment->accepts_join_requests,
        ]);
    }

    // Staff tag szerepkörének módosítása
    public function promoteStaff(Request $request, int $establishmentId, int $staffId)
    {
        $user = $request->user();

        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json(['message' => 'Intézmény nem található!'], 404);
        }

        // Csak a tulajdonos módosíthatja a staff szerepköröket.
        if ($establishment->user_id != $user->id) {
            return response()->json(['message' => 'Csak az intézmény tulajdonosa módosíthatja a szerepköröket!'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,teacher',
        ], [
            'role.required' => 'A szerepkör megadása kötelező.',
            'role.in' => 'A szerepkör csak admin vagy teacher lehet.',
        ]);

        // Megkeressük a módosítandó staff rekordot.
        $staff = Staff::where('id', $staffId)
            ->where('establishment_id', $establishmentId)
            ->first();

        if (!$staff) {
            return response()->json(['message' => 'A megadott staff tag nem található ebben az intézményben!'], 404);
        }

        // A tulajdonos saját magát ne lehessen lefokozni
        if ($staff->user_id == $user->id) {
            return response()->json(['message' => 'Az intézmény tulajdonosa nem módosíthatja a saját szerepkörét!'], 422);
        }

        $staff->role = $validated['role'];
        $staff->save();

        if ($validated['role'] === 'admin') {
            $roleLabel = 'Adminisztrátor';
        } else {
            $roleLabel = 'Tanár';
        }

        return response()->json([
            'message' => "Szerepkör sikeresen frissítve: {$roleLabel}.",
            'staff_id' => $staff->id,
            'role' => $staff->role,
        ]);
    }

    // Intézmény alapadatainak frissítése
    public function updateEstablishmentDetails(Request $request, int $establishmentId)
    {
        $user = $request->user();

        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json(['message' => 'Intézmény nem található!'], 404);
        }

        if ($establishment->user_id != $user->id) {
            return response()->json(['message' => 'Csak az intézmény tulajdonosa módosíthatja az intézmény adatait!'], 403);
        }

        $validated = $request->validate([
            'title' => "sometimes|required|string|max:255|unique:establishments,title,{$establishment->id}",
            'description' => 'sometimes|nullable|string',
            'website' => 'sometimes|nullable|url|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:32',
            'address' => 'sometimes|nullable|string|max:255',
        ], [
            'title.required' => 'Az intézmény neve kötelező.',
            'title.string' => 'Az intézmény neve szöveges érték kell legyen.',
            'title.max' => 'Az intézmény neve nem lehet hosszabb 255 karakternél.',
            'title.unique' => 'Már létezik ilyen nevű intézmény.',
            'description.string' => 'Az intézmény leírása szöveges érték kell legyen.',
            'website.url' => 'A weboldal URL formátumú kell legyen.',
            'website.max' => 'A weboldal nem lehet hosszabb 255 karakternél.',
            'email.email' => 'Az email cím formátuma érvénytelen.',
            'email.max' => 'Az email cím nem lehet hosszabb 255 karakternél.',
            'phone.string' => 'A telefonszám szöveges érték kell legyen.',
            'phone.max' => 'A telefonszám nem lehet hosszabb 32 karakternél.',
            'address.string' => 'A cím szöveges érték kell legyen.',
            'address.max' => 'A cím nem lehet hosszabb 255 karakternél.',
        ]);

        // Csak a validált mezőket írjuk vissza.
        $establishment->fill($validated);
        $establishment->save();

        return response()->json([
            'message' => 'Intézmény adatai sikeresen frissítve.',
            'data' => $establishment,
        ]);
    }

    // Intézmény tulajdonjogának átadása másik admin tagnak
    public function transferOwnership(Request $request, int $establishmentId)
    {
        $user = $request->user();

        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return response()->json(['message' => 'Intézmény nem található!'], 404);
        }

        if ($establishment->user_id != $user->id) {
            return response()->json(['message' => 'Csak az intézmény tulajdonosa adhatja át a tulajdonjogot!'], 403);
        }

        $validated = $request->validate([
            'new_owner_user_id' => 'required|integer|exists:users,id',
        ], [
            'new_owner_user_id.required' => 'Az új tulajdonos megadása kötelező.',
            'new_owner_user_id.integer' => 'Az új tulajdonos azonosítójának egész számnak kell lennie.',
            'new_owner_user_id.exists' => 'A megadott felhasználó nem létezik.',
        ]);

        $newOwnerUserId = $validated['new_owner_user_id'];

        if ($newOwnerUserId == $user->id) {
            return response()->json(['message' => 'A tulajdonjog már nálad van.'], 422);
        }

        $targetStaff = Staff::where('establishment_id', $establishmentId)
            ->where('user_id', $newOwnerUserId)
            ->first();

        if (!$targetStaff) {
            return response()->json(['message' => 'Az új tulajdonosnak az intézmény tanárának kell lennie!'], 422);
        }

        // A tulajdonosváltás és a szerepkör frissítése egyszerre történik.
        DB::transaction(function () use ($establishment, $targetStaff, $newOwnerUserId) {
            $establishment->user_id = $newOwnerUserId;
            $establishment->save();

            if ($targetStaff->role !== 'admin') {
                $targetStaff->role = 'admin';
                $targetStaff->save();
            }
        });

        return response()->json([
            'message' => 'Tulajdonjog sikeresen átadva.',
            'establishment_id' => $establishment->id,
            'new_owner_user_id' => $newOwnerUserId,
        ]);
    }
}
