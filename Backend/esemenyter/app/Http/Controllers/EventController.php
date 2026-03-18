<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventShown;
use App\Models\EventRequest;
use Illuminate\Support\Facades\DB;

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
            'establishment_id' => 'required|exists:establishments,id',
            'collab_establishment_ids' => 'array|required_if:type,global|min:1',
            'collab_establishment_ids.*' => 'exists:establishments,id',
            'target_group' => 'nullable|string|in:sajat_osztaly,evfolyam,teljes_iskola|required_if:type,local',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'users' => 'array|required_if:type,local',
            'users.*' => 'exists:users,id',
        ], [
            'type.required' => 'A típus megadása kötelező.',
            'type.in' => 'A típusnak "local" vagy "global" értéknek kell lennie.',
            'establishment_id.required' => 'Az intézmény azonosító megadása kötelező.',
            'establishment_id.exists' => 'A megadott intézmény nem található.',
            'collab_establishment_ids.array' => 'A kollab_establishment_ids mezőnek tömbnek kell lennie.',
            'collab_establishment_ids.required_if' => 'A kollaboráló intézmények megadása kötelező globális esemény esetén.',
            'collab_establishment_ids.min' => 'Legalább egy kollaboráló intézményt meg kell adni globális eseményhez.',
            'collab_establishment_ids.*.exists' => 'A megadott kollaboráló intézmény nem található.',
            'target_group.required_if' => 'Helyi eseménynél a célcsoport megadása kötelező.',
            'target_group.in' => 'A célcsoport értéke csak sajat_osztaly, evfolyam vagy teljes_iskola lehet.',
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
            'users.required_if' => 'A felhasználók megadása kötelező helyi esemény esetén.',
            'users.*.exists' => 'A megadott felhasználó nem található.',
        ]);


        $users = $validated['users'] ?? [];
        $collabIds = collect($validated['collab_establishment_ids'] ?? [])
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0 && $id !== (int) $validated['establishment_id'])
            ->unique()
            ->values()
            ->all();

        if ($validated['type'] == 'local') {
            if (!$this->isStaffEstablishment($user->id, $validated['establishment_id'])) {
                return response()->json(['message' => 'nem jogosult'], 401);
            }

            $event = DB::transaction(function () use ($user, $validated, $users) {
                $event = Event::create([
                    'user_id' => $user->id,
                    'establishment_id' => $validated['establishment_id'],
                    'target_group' => $validated['target_group'] ?? 'teljes_iskola',
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
                    if ((int) $userId === (int) $user->id) {
                        continue;
                    }

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
            if (!$this->isAdminEstablishment($user->id, $validated['establishment_id'])) {
                return response()->json(['message' => 'nem jogosult'], 401);
            }

            if (empty($collabIds)) {
                return response()->json(['message' => 'Legalább egy másik intézményt ki kell választani globális eseményhez.'], 422);
            }

            $users = DB::table('students')
                ->where('establishment_id', $validated['establishment_id'])
                ->pluck('user_id')
                ->merge(
                    DB::table('staffs')
                        ->where('establishment_id', $validated['establishment_id'])
                        ->pluck('user_id')
                )
                ->push($user->id)
                ->unique()
                ->values()
                ->all();

            $event = DB::transaction(function () use ($user, $validated, $users, $collabIds) {
                $event = Event::create([
                    'user_id' => $user->id,
                    'establishment_id' => $validated['establishment_id'],
                    'target_group' => null,
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
                    if ((int) $userId === (int) $user->id) {
                        continue;
                    }

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
        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'nem jogosult'], 403);
        }

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'action' => 'required|in:accept,reject',
            'users' => 'array',
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
                $users = DB::table('students')
                    ->where('establishment_id', $establishmentId)
                    ->pluck('user_id')
                    ->merge(
                        DB::table('staffs')
                            ->where('establishment_id', $establishmentId)
                            ->pluck('user_id')
                    )
                    ->unique()
                    ->values()
                    ->all();

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

        if (!$this->isAdminEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        $collabEventIds = EventRequest::where('establishment_id', $establishmentId)
            ->distinct()
            ->pluck('event_id');

        $events = Event::whereIn('id', $collabEventIds)
            ->orderBy('start_date', 'asc')
            ->get();

        if ($events->isNotEmpty()) {
            $creatorNames = DB::table('users')
                ->whereIn('id', $events->pluck('user_id')->filter()->unique()->values())
                ->pluck('name', 'id');

            $events->each(function ($event) use ($creatorNames) {
                $event->creator_name = $creatorNames[$event->user_id] ?? 'Ismeretlen szervező';
            });
        }

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
        if (!$this->isMemberEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        $hasClassMembership = DB::table('class_students')
            ->join('classes', 'classes.id', '=', 'class_students.class_id')
            ->where('class_students.user_id', $user->id)
            ->where('classes.establishment_id', $establishmentId)
            ->exists();

        if ($hasClassMembership) {
            $visibleEventIds = EventShown::where('establishment_id', $establishmentId)
                ->distinct()
                ->pluck('event_id');

            $events = Event::whereIn('id', $visibleEventIds)
                ->orderBy('start_date', 'asc')
                ->get();
        } else {
            $directlyAssignedEventIds = EventShown::where('establishment_id', $establishmentId)
                ->where('user_id', $user->id)
                ->select('event_id');

            $schoolWideEventIds = EventShown::where('establishment_id', $establishmentId)
                ->select('event_id');

            $events = Event::where(function ($query) use ($directlyAssignedEventIds, $schoolWideEventIds) {
                    $query->whereIn('id', $directlyAssignedEventIds)
                        ->orWhere(function ($schoolWideQuery) use ($schoolWideEventIds) {
                            $schoolWideQuery->where('type', 'local')
                                ->where('target_group', 'teljes_iskola')
                                ->whereIn('id', $schoolWideEventIds);
                        });
                })
                ->orderBy('start_date', 'asc')
                ->get();
        }

        if ($events->isNotEmpty()) {
            $creatorNames = DB::table('users')
                ->whereIn('id', $events->pluck('user_id')->filter()->unique()->values())
                ->pluck('name', 'id');

            $events->each(function ($event) use ($creatorNames) {
                $event->creator_name = $creatorNames[$event->user_id] ?? 'Ismeretlen szervező';
            });
        }

        $eventIds = $events->pluck('id')->all();

        $feedbackStatsByEvent = collect();
        $userFeedbackByEvent = collect();
        $commentCountsByEvent = collect();

        if (!empty($eventIds)) {
            $feedbackStatsByEvent = DB::table('event_feedbacks')
                ->select(
                    'event_id',
                    DB::raw("SUM(CASE WHEN answer = 'y' THEN 1 ELSE 0 END) as attending_count"),
                    DB::raw("SUM(CASE WHEN answer = 'n' THEN 1 ELSE 0 END) as not_attending_count")
                )
                ->whereIn('event_id', $eventIds)
                ->groupBy('event_id')
                ->get()
                ->keyBy('event_id');

            $userFeedbackByEvent = DB::table('event_feedbacks')
                ->whereIn('event_id', $eventIds)
                ->where('user_id', $user->id)
                ->pluck('answer', 'event_id');

            $commentCountsByEvent = DB::table('event_messages')
                ->select('event_id', DB::raw('COUNT(*) as comment_count'))
                ->whereIn('event_id', $eventIds)
                ->groupBy('event_id')
                ->get()
                ->keyBy('event_id');
        }

        $events = $events->map(function ($event) use ($feedbackStatsByEvent, $userFeedbackByEvent, $commentCountsByEvent) {
            $stats = $feedbackStatsByEvent->get($event->id);
            $commentStats = $commentCountsByEvent->get($event->id);

            $event->attending_count = (int) ($stats->attending_count ?? 0);
            $event->not_attending_count = (int) ($stats->not_attending_count ?? 0);
            $event->participant_count = (int) ($stats->attending_count ?? 0);
            $event->participants = (int) ($stats->attending_count ?? 0);
            $event->comment_count = (int) ($commentStats->comment_count ?? 0);
            $event->user_participation = $userFeedbackByEvent->get($event->id);

            return $event;
        });

        return response()->json([
            'events' => $events
        ]);
    }

    public function setParticipation(Request $request, int $eventId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        $validated = $request->validate([
            'answer' => 'required|in:y,n',
        ], [
            'answer.required' => 'A válasz megadása kötelező.',
            'answer.in' => 'A válasz csak "y" vagy "n" lehet.',
        ]);

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Az esemény nem található.'], 404);
        }

        $canSeeEvent = EventShown::where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$canSeeEvent) {
            return response()->json(['message' => 'nem jogosult'], 403);
        }

        DB::table('event_feedbacks')->updateOrInsert(
            [
                'event_id' => $eventId,
                'user_id' => $user->id,
            ],
            [
                'answer' => $validated['answer'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $attendingCount = DB::table('event_feedbacks')
            ->where('event_id', $eventId)
            ->where('answer', 'y')
            ->count();

        $notAttendingCount = DB::table('event_feedbacks')
            ->where('event_id', $eventId)
            ->where('answer', 'n')
            ->count();

        return response()->json([
            'message' => 'Részvételi válasz mentve.',
            'answer' => $validated['answer'],
            'attending_count' => $attendingCount,
            'not_attending_count' => $notAttendingCount,
            'participant_count' => $attendingCount,
        ]);
    }
}
