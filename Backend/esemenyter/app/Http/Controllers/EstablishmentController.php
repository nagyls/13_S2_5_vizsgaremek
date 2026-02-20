<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Establishment;
use App\Models\Staff;


class EstablishmentController extends Controller
{
    //
    public function store(Request $request)
    {

        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
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

        $Staff = Staff::create([
            'role' => 'admin',
            'establishment_id' => $establishment->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Intézmény regisztrálva!',
            'data' => $establishment
        ], 201);
    }


    public function getEstablishments(Request $request)
    {
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
        $establishments = Establishment::where('user_id', $user->id)
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $establishments
        ]);
    }
}
