<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventShown;

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

        if($validated['type'] === 'local'){
            $event = Event::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            ]);
            EventShown::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'establishment_id' => $user->establishment_id,
            ]);
        }


        
        foreach ($user->class->students as $student) {
            EventShown::create([
                'event_id' => $event->id,
                'user_id' => $student->id,
                'establishment_id' => $student->establishment_id,
            ]);
        }


        return response()->json([
            'message' => 'Esemény létrehozva',
            'event' => $event
        ], 201);
    }
    public function getEvents(Request $request, int $establishmentId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }
        if(!$this->isMemberEstablishment($user, $establishmentId)){
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        $visibleEventIds = EventShown::where('user_id', $user->id)
            ->where('establishment_id', $establishmentId)
            ->distinct()
            ->pluck('event_id');

        $events = Event::whereIn('id', $visibleEventIds)
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'events' => $events
        ]);
    }
}
