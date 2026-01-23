<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use Carbon\Carbon;

class EventController extends Controller
{
    private function determineStatus($start, $end)
    {
        $now = Carbon::now();

        if ($now->lt($start)) {
            return 'upcoming';
        }

        if ($now->between($start, $end)) {
            return 'ongoing';
        }

        return 'ended';
    }
    //
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'type' => 'required|in:local,global',
            'scope_mode' => 'nullable|string',
            'establishment_ids' => 'required|array',
            'establishment_ids.*' => 'integer|exists:establishments,id',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'integer|exists:classes,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($user->role === 'teacher' && $validated['type'] !== 'local') {
            return response()->json([
                'message' => 'Tanár csak helyi eseményt hozhat létre'
            ], 403);
        }

        $status = $this->determineStatus(
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date'])
        );

        $event = Event::create([
            'users_id' => $user->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $status,
        ]);

        $event->establishments()->sync($validated['establishment_ids']);
        $event->classes()->sync($validated['class_ids'] ?? []);

        return response()->json([
            'message' => 'Esemény létrehozva',
            'event' => $event
        ], 201);
    }
    
}
