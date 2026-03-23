<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventShown;
use App\Models\EventRequest;
use Illuminate\Support\Facades\DB;

class sablon extends Controller
{


    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'type' => 'required|in:local,global',
            'establishment_id' => 'required|exists:establishments,id',
            'collab_establishment_ids' => 'array|required_if:type,global',
            'collab_establishment_ids.*' => 'exists:establishments,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'users' => 'array|required',
            'users.*' => 'exists:users,id',
        ], [
            'type.required' => 'A típus megadása kötelező.',
            'type.in' => 'A típusnak "local" vagy "global" értéknek kell lennie.',
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.exists' => 'A megadott intézmény nem található.',
            'collab_establishment_ids.array' => 'A kollab_establishment_ids mezőnek tömbnek kell lennie.',
            'collab_establishment_ids.required_if' => 'A kollaboráló intézmények megadása kötelező globális esemény esetén.',
            'collab_establishment_ids.*.exists' => 'A megadott kollaboráló intézmény nem található.',
            'title.required' => 'A cím megadása kötelező.',
            'title.string' => 'A címnek szövegnek kell lennie.',
            'title.max' => 'A cím nem lehet hosszabb, mint :max karakter.',
            'description.required' => 'A leírás megadása kötelező.',
            'description.string' => 'A leírásnak szövegnek kell lennie.',
            'content.string' => 'A tartalomnak szövegnek kell lennie.',
            'start_date.required' => 'A kezdő dátum megadása kötelező.',
            'start_date.date' => 'A kezdő dátumnak érvényes dátumnak kell lennie.',
            'end_date.required' => 'A befejező dátum megadása kötelező.',
            'end_date.date' => 'A befejező dátumnak érvényes dátumnak kell lennie.',
            'end_date.after' => 'A befejező dátumnak a kezdő dátunál később kell lennie.',
            'users.array' => 'A users mezőnek tömbnek kell lennie.',
            'users.required' => 'A felhasználók megadása kötelező.',
            'users.*.exists' => 'A megadott felhasználó nem található.',
        ]);


        $users = $validated['users'];
        $collabIds = $validated['collab_establishment_ids'] ?? [];

        if ($validated['type'] == 'local') {
            if (!$this->isStaffEstablishment($user, $validated['establishment_id'])) {
                return response()->json(['message' => 'nem jogosult'], 401);
            }

            $event = DB::transaction(function () use ($user, $validated, $users) {
                $event = Event::create([
                    'user_id' => $user->id,
                    'type' => $validated['type'],
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'content' => $validated['content'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                ]);

                $shownRows = [];

                // készítő láthatóság
                $shownRows[] = [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'establishment_id' => $validated['establishment_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // eredeti intézmény felhasználói láthatóság
                foreach ($users as $userId) {
                    if ($this->isMemberEstablishment($userId, $validated['establishment_id'])) {
                        $shownRows[] = [
                            'event_id' => $event->id,
                            'user_id' => $userId,
                            'establishment_id' => $validated['establishment_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($shownRows)) {
                    EventShown::insert($shownRows);
                }

                return $event;
            });
        }

        if ($validated['type'] == 'global') {
            if (!$this->isAdminEstablishment($user, $validated['establishment_id'])) {
                return response()->json(['message' => 'nem jogosult'], 401);
            }

            $event = DB::transaction(function () use ($user, $validated, $users, $collabIds) {
                $event = Event::create([
                    'user_id' => $user->id,
                    'type' => $validated['type'],
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'content' => $validated['content'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                ]);

                $shownRows = [];

                // készítő láthatóság
                $shownRows[] = [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'establishment_id' => $validated['establishment_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Intézményi felhasználók láthatósága
                foreach ($users as $userId) {
                    if ($this->isMemberEstablishment($userId, $validated['establishment_id'])) {
                        $shownRows[] = [
                            'event_id' => $event->id,
                            'user_id' => $userId,
                            'establishment_id' => $validated['establishment_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }


                // kollaborációs kérelmek létrehozása
                if (!empty($collabIds)) {
                    foreach ($collabIds as $collabId) {
                        EventRequest::create([
                            'event_id' => $event->id,
                            'establishment_id' => $collabId,
                        ]);
                    }
                }

                if (!empty($shownRows)) {
                    EventShown::insert($shownRows);
                }
                return $event;
            });
        }

        return response()->json([
            'message' => 'Esemény létrehozva',
            'event' => $event
        ], 201);
    }

    public function handleCollabEvents(Request $request, int $establishmentId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }
        if (!$this->isAdminEstablishment($user, $establishmentId)) {
            return response()->json(['message' => 'nem jogosult'], 403);
        }

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'action' => 'required|in:accept,reject',
            'users' => 'array|required_if:action,accept',
            'users.*' => 'exists:users,id',
        ], [
            'event_id.required' => 'Az esemény azonosító megadása kötelező.',
            'event_id.exists' => 'A megadott esemény nem található.',
            'action.required' => 'A művelet megadása kötelező.',
            'action.in' => 'A műveletnek "accept" vagy "reject" értéknek kell lennie.',
        ]);

        $eventId = $validated['event_id'];

        DB::transaction(function () use ($validated, $establishmentId, $eventId) {
            //kérelem törlése
            EventRequest::where('event_id', $eventId)
                ->where('establishment_id', $establishmentId)
                ->delete();

            if ($validated['action'] === 'accept') {
                $users = $validated['users'] ?? [];
                foreach ($users as $userId) {
                    if ($this->isMemberEstablishment($userId, $establishmentId)) {
                        EventShown::firstOrCreate([
                            'event_id' => $eventId,
                            'user_id' => $userId,
                            'establishment_id' => $establishmentId,
                        ]);
                    }
                }
            }
        });

        return response()->json([
            'message' => 'Művelet végrehajtva',
        ], 200);
    }

    public function getCollabEvents(Request $request, int $establishmentId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        if (!$this->isAdminEstablishment($user, $establishmentId)) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        $collabEventIds = EventRequest::where('establishment_id', $establishmentId)
            ->distinct()
            ->pluck('event_id');

        $events = Event::whereIn('id', $collabEventIds)
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'events' => $events
        ]);
    }



    public function getEvents(Request $request, int $establishmentId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }
        if (!$this->isMemberEstablishment($user, $establishmentId)) {
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
