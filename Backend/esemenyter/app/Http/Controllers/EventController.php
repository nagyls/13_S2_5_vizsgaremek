<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;

class EventController extends Controller
{
   

    public function store(Request $request)
    {
        $user = $request->user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'type' => 'required|in:local,global',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);



        $event = Event::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        return response()->json([
            'message' => 'Esemény létrehozva',
            'event' => $event
        ], 201);
    }
}
