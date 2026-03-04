<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventShown;
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
            'end_date' => 'required|date|after:start_date',
            'chat_enabled' => 'boolean',
            'establishment_ids' => 'array',
            'establishment_ids.*' => 'integer|exists:establishments,id',
            'class_ids' => 'array',
            'class_ids.*' => 'integer|exists:classes,id',
        ],[
            'type.required' => 'Az esemény típusának megadása kötelező.',
            'type.in' => 'Az esemény típusa csak "local" vagy "global" lehet.',
            'title.required' => 'Az esemény címének megadása kötelező.',
            'title.string' => 'Az esemény címének szöveges értéknek kell lennie.',
            'title.max' => 'Az esemény címének legfeljebb 255 karakter hosszúnak kell lennie.',
            'description.required' => 'Az esemény leírásának megadása kötelező.',
            'description.string' => 'Az esemény leírásának szöveges értéknek kell lennie.',
            'content.string' => 'Az esemény tartalmának szöveges értéknek kell lennie.',
            'start_date.required' => 'Az esemény kezdő dátumának megadása kötelező.',
            'start_date.date' => 'Az esemény kezdő dátumának érvényes dátumnak kell lennie.',
            'end_date.required' => 'Az esemény záró dátumának megadása kötelező.',
            'end_date.date' => 'Az esemény záró dátumának érvényes dátumnak kell lennie.',
            'end_date.after' => 'Az esemény záró dátumának a kezdő dátummal későbbinek kell lennie.',
        ]);



        $event = Event::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        return response()->json([
            'message' => 'Esemény létrehozva',
            'event' => $event
        ], 201);
    }
    public function getEvents(Request $request)
    {
        $user = $request->user();
        $events = Event::where('end_date', '>=', Carbon::now())
            ->where(function ($query) use ($user) {
                if ($user) {
                    $visibleEventIds = EventShown::where(function ($query2) use ($user) {
                        $query2->where('user_id', $user->id);
                    })->pluck('event_id');
                    $query->orWhereIn('id', $visibleEventIds);
                }
            })->get();
        return response()->json([
            'events' => $events
        ]);
    }  
}
