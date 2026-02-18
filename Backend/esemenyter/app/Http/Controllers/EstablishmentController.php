<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Establishment;
use App\Models\Personel;


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
        ]);
        $validated['user_id'] = $request->user()->id;
        $establishment = Establishment::create($validated);

        $personel = Personel::create([
            'role' => 'admin',
            'establishment_id' => $establishment->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Intézmény regisztrálva!'], 201);
    }


    public function getEstablishments(Request $request)
    {
        $query = Establishment::query();
        if ($request->has('search') && !empty($request->search) && $request->has('settlement_id') && !empty($request->settlement_id)) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%")->where('settlement_id', '=', "{$request->settlement_id}");

        $establishment = $query->orderBy('title')->get();
        return response()->json([
            'id' => $establishment->pluck('id'),
            'title' => $establishment->pluck('title'),
        ]);
    }

    // public function getEstablishments(Request $request)
    // {
    //     $query = Establishment::query();
    
    //     if ($request->filled('settlement_id')) {
    //         $query->where('settlement_id', $request->settlement_id);
    //     }
    
    //     if ($request->filled('search')) {
    //         $query->where('title', 'LIKE', "%{$request->search}%");
    //     }
    
    //     return response()->json([
    //         'success' => true,
    //         'data' => $query->orderBy('title')->get()
    //     ]);
    // }

    public function getEstablishmentbyId($id)
    {
        $establishment = Establishment::find($id);

        return response()->json([
            'data' => $establishment
        ]);
    }
}
