<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Establishment;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Mime\Message;

class EstablishmentController extends Controller
{
    //
    public function store(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('establishments', 'title')],
            'description' => ['nullable', 'string'],
            'settlement_id' => ['required', 'exists:settlements,id'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
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
        ]);
        $validated['user_id'] = $request->user()->id;
        $establishment = Establishment::create($validated);

        $staff = Staff::create([
            'role' => 'admin',
            'establishment_id' => $establishment->id,
            'user_id' => $request->user()->id,
        ]);

        User::where('id', $request->user()->id)->update(['establishment_id' => $establishment->id]);

        return response()->json([
            'message' => 'Intézmény regisztrálva!',
            'data' => $establishment
        ], 201);
    }


    public function getEstablishments(Request $request)
    {
        if ($request->has('region_id') && !empty($request->region_id)) {
            $regionId = (int) $request->region_id;

            $query = Establishment::query()
                ->join('settlements', 'settlements.id', '=', 'establishments.settlement_id')
                ->join('inner_regions', 'inner_regions.id', '=', 'settlements.inner_region_id')
                ->where('inner_regions.region_id', $regionId)
                ->select('establishments.id', 'establishments.title')
                ->distinct();

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
                    ];
                })->values(),
            ]);
        }

        $query = Establishment::query();
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
                ];
            })->values(),
        ]);
    }
    public function getEstablishmentbyId($id)
    {
        $establishment = Establishment::find($id);

        return response()->json([
            'data' => $establishment
        ]);
    }
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
                'staffs.role as membership_role'
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
                DB::raw("'student' as membership_role")
            )
            ->get();

        $establishments = $staffMemberships
            ->concat($studentMemberships)
            ->unique('id')
            ->sortBy(function ($item) {
                return mb_strtolower((string) $item->title);
            })
            ->values();

        return response()->json([
            'data' => $establishments->map(function ($item) use ($user) {
                return [
                    'id' => (int) $item->id,
                    'title' => $item->title,
                    'address' => $item->address,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'website' => $item->website,
                    'role' => $item->membership_role,
                    'is_current' => (int) $user->establishment_id === (int) $item->id,
                ];
            })->values()
        ]);
    }

    public function switchEstablishment(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }

        $validated = $request->validate([
            'establishment_id' => ['required', 'integer', 'exists:establishments,id'],
        ], [
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.integer' => 'Az intézmény azonosítónak egész számnak kell lennie.',
            'establishment_id.exists' => 'Nem létező intézmény.',
        ]);

        $establishmentId = (int) $validated['establishment_id'];

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

    public function getRole(Request $request, $establishmentId)
    {
        $user = $request->user();

        if ($this->isMemberEstablishment($user->id, $establishmentId)) {
            if ($this->isStaffEstablishment($user->id, $establishmentId)) {
                if ($this->isAdminEstablishment($user->id, $establishmentId)) {
                    return response()->json([
                        'role' => 'admin',
                    ]);
                }
                return response()->json([
                    'role' => 'teacher',
                ]);
            }
            return response()->json([
                'role' => 'student',
            ]);
        }
        return response()->json([
            'message' => 'Nem tagja egy intézménynek sem!',
            403
        ]);
    }
}
