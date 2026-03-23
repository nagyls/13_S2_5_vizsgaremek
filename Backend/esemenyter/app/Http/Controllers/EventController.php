<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventShown;
use App\Models\EventRequest;
use Illuminate\Support\Carbon;  ////SZAKKÖR MÓDOSÍTÁS
use App\Models\ClassModel;
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
            'target_group' => 'required_if:type,local,global|in:osztaly_szintu,evfolyam_szintu,teljes_iskola',
            'selected_class_ids' => 'array|required_if:target_group,osztaly_szintu|min:1',
            'selected_class_ids.*' => 'integer|exists:classes,id',
            'selected_grade_ids' => 'array|required_if:target_group,evfolyam_szintu|min:1',
            'selected_grade_ids.*' => 'integer|min:1',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',

            //SZAKKÖR MÓDOSÍTÁS
            'is_recurring' => 'sometimes|boolean',
            'recurrence_frequency' => 'nullable|required_if:is_recurring,1|in:weekly',
            'recurrence_until' => 'nullable|required_if:is_recurring,1|date',
            //

            'users' => 'array|required_if:type,local,global',
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
            'target_group.required_if' => 'Eseménynél a célcsoport megadása kötelező.',
            'target_group.in' => 'A célcsoport értéke csak osztaly_szintu, evfolyam_szintu vagy teljes_iskola lehet.',
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
            'users.required_if' => 'A felhasználók megadása kötelező.',

            //SZAKKÖR MÓDOSÍTÁS
            'is_recurring.boolean' => 'Az ismétlődés mező értéke csak igaz vagy hamis lehet.',
            'recurrence_frequency.required_if' => 'Ismétlődő eseménynél meg kell adni a gyakoriságot.',
            'recurrence_frequency.in' => 'Jelenleg csak heti ismétlődés támogatott.',
            'recurrence_until.required_if' => 'Ismétlődő eseménynél záró dátum megadása kötelező.',
            'recurrence_until.date' => 'Az ismétlődés záró dátuma érvényes dátum legyen.',
            //

            'users.array' => 'A users mezőnek tömbnek kell lennie.',
            'users.min' => 'Legalább :min felhasználót meg kell adni.',
            'users.*.integer' => 'A felhasználó azonosítónak számnak kell lennie.',
            'users.*.distinct' => 'A felhasználók között nem lehetnek ismétlődő értékek.',
            'users.*.exists' => 'A megadott felhasználó nem található.',
        ]);

        //SZAKKÖR MÓDOSÍTÁS
        $isRecurring = (bool) ($validated['is_recurring'] ?? false);

        if ($isRecurring) {
            $recurrenceUntilEndOfDay = Carbon::parse($validated['recurrence_until'])->endOfDay();
            $firstStart = Carbon::parse($validated['start_date']);

            if ($recurrenceUntilEndOfDay->lt($firstStart)) {
                return response()->json([
                    'message' => 'Az ismétlődés záró dátuma nem lehet korábbi, mint az első alkalom napja.'
                ], 422);
            }
        }

        if ($isRecurring && $validated['type'] !== 'local') {
            return response()->json([
                'message' => 'Ismétlődő eseményt jelenleg csak helyi eseménynél lehet létrehozni.'
            ], 422);
        }
        //

        $users = $validated['users'] ?? [];
        $collabIds = $validated['collab_establishment_ids'] ?? [];
        $selectedClassIds = array_values(array_map('intval', $validated['selected_class_ids'] ?? []));
        $selectedGradeIds = array_values(array_map('intval', $validated['selected_grade_ids'] ?? []));

        if (($validated['target_group'] ?? null) === 'osztaly_szintu' && !empty($selectedClassIds)) {
            $validClassIds = ClassModel::where('establishment_id', $validated['establishment_id'])
                ->whereIn('id', $selectedClassIds)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            if (count($validClassIds) !== count($selectedClassIds)) {
                return response()->json([
                    'message' => 'Egy vagy több osztály nem tartozik az intézményhez!'
                ], 400);
            }
        }

        if (($validated['target_group'] ?? null) === 'evfolyam_szintu' && !empty($selectedGradeIds)) {
            $validGrades = ClassModel::where('establishment_id', $validated['establishment_id'])
                ->whereIn('grade', $selectedGradeIds)
                ->pluck('grade')
                ->map(fn($grade) => (int) $grade)
                ->unique()
                ->values()
                ->toArray();

            if (count($validGrades) !== count(array_unique($selectedGradeIds))) {
                return response()->json([
                    'message' => 'Egy vagy több évfolyam nem található az intézményben!'
                ], 400);
            }
        }

        if ($validated['type'] == 'local') {
            if (!$this->isStaffEstablishment($user->id, $validated['establishment_id'])) {
                return response()->json(['message' => 'nem jogosult'], 403);
            }

            //eredeti: 
        //  $event = DB::transaction(function () use ($user, $validated, $users) {
            $event = DB::transaction(function () use ($user, $validated, $users, $isRecurring) { //SZAKKÖR MÓDOSÍTÁS
                $event = Event::create([
                    'user_id' => $user->id,
                    'establishment_id' => $validated['establishment_id'],
                    'type' => $validated['type'],
                    'target_group' => $validated['target_group'] ?? null,
                    'target_class_ids' => ($validated['target_group'] ?? null) === 'osztaly_szintu' ? array_values(array_map('intval', $validated['selected_class_ids'] ?? [])) : null,
                    'target_grade_ids' => ($validated['target_group'] ?? null) === 'evfolyam_szintu' ? array_values(array_map('intval', $validated['selected_grade_ids'] ?? [])) : null,
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'content' => $validated['content'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],

                    //SZAKKÖR MÓDOSÍTÁS
                    'status' => 'upcoming',
                    'is_recurring' => $isRecurring,
                    'recurrence_frequency' => $isRecurring ? ($validated['recurrence_frequency'] ?? 'weekly') : null,
                    'recurrence_until' => $isRecurring ? $validated['recurrence_until'] : null,
                    'recurrence_parent_event_id' => null,
                    //
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

                //SZAKKÖR MÓDOSÍTÁS
                if ($isRecurring) {
                    $this->createWeeklyOccurrences(
                        $event,
                        $validated['recurrence_until'],
                        $shownRows
                    );
                }
                //

                return $event;
            });
        }

        if ($validated['type'] == 'global') {
            if (!$this->isAdminEstablishment($user->id, $validated['establishment_id'])) {
                return response()->json(['message' => 'nem jogosult'], 403);
            }

            $event = DB::transaction(function () use ($user, $validated, $users, $collabIds) {
                $event = Event::create([
                    'user_id' => $user->id,
                    'establishment_id' => $validated['establishment_id'],
                    'type' => $validated['type'],
                    'target_group' => $validated['target_group'] ?? null,
                    'target_class_ids' => ($validated['target_group'] ?? null) === 'osztaly_szintu' ? array_values(array_map('intval', $validated['selected_class_ids'] ?? [])) : null,
                    'target_grade_ids' => ($validated['target_group'] ?? null) === 'evfolyam_szintu' ? array_values(array_map('intval', $validated['selected_grade_ids'] ?? [])) : null,
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'content' => $validated['content'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],

                    //SZAKKÖR MÓDOSÍTÁS
                    'status' => 'upcoming',
                    'is_recurring' => false,
                    'recurrence_frequency' => null,
                    'recurrence_until' => null,
                    'recurrence_parent_event_id' => null,
                    //

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

    //SZAKKÖR MÓDOSÍTÁS
    private function createWeeklyOccurrences(Event $event, string $recurrenceUntil, array $shownRows): void
    {
        $startDate = Carbon::parse($event->start_date);
        $endDate = Carbon::parse($event->end_date);
        $untilDate = Carbon::parse($recurrenceUntil)->endOfDay();

        $nextStart = $startDate->copy()->addWeek();
        $nextEnd = $endDate->copy()->addWeek();

        $maxOccurrences = 104;
        $iteration = 0;

        while ($nextStart->lte($untilDate) && $iteration < $maxOccurrences) {
            $occurrence = Event::create([
                'user_id' => $event->user_id,
                'establishment_id' => $event->establishment_id,
                'target_group' => $event->target_group,
                'type' => $event->type,
                'title' => $event->title,
                'description' => $event->description,
                'content' => $event->content,
                'start_date' => $nextStart->copy()->toDateTimeString(),
                'end_date' => $nextEnd->copy()->toDateTimeString(),
                'status' => $event->status ?: 'upcoming',
                'is_recurring' => true,
                'recurrence_frequency' => 'weekly',
                'recurrence_until' => $event->recurrence_until,
                'recurrence_parent_event_id' => $event->id,
            ]);

            if (!empty($shownRows)) {
                $occurrenceShownRows = array_map(function ($row) use ($occurrence) {
                    return [
                        'event_id' => $occurrence->id,
                        'user_id' => $row['user_id'],
                        'establishment_id' => $row['establishment_id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $shownRows);

                EventShown::insert($occurrenceShownRows);
            }

            $nextStart->addWeek();
            $nextEnd->addWeek();
            $iteration++;
        }
    }
    //

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
            'users' => 'array|required_if:action,accept',
            'users.*' => 'integer|distinct|exists:users,id',
        ], [
            'event_id.required' => 'Az esemény azonosító megadása kötelező.',
            'event_id.exists' => 'A megadott esemény nem található.',
            'action.required' => 'A művelet megadása kötelező.',
            'action.in' => 'A műveletnek "accept" vagy "reject" értéknek kell lennie.',
            'users.array' => 'A users mezőnek tömbnek kell lennie.',
            'users.required_if' => 'A felhasználók megadása kötelező az elfogadás művelet esetén.',
            'users.*.integer' => 'A felhasználó azonosítónak számnak kell lennie.',
            'users.*.distinct' => 'A felhasználók között nem lehetnek ismétlődő értékek.',
            'users.*.exists' => 'A megadott felhasználó nem található.',
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
                        $hasFavouriteInAnyEstablishment = EventShown::where('event_id', $eventId)
                            ->where('user_id', $userId)
                            ->where('is_favourite', true)
                            ->exists();

                        $eventShown = EventShown::firstOrCreate([
                            'event_id' => $eventId,
                            'user_id' => $userId,
                            'establishment_id' => $establishmentId,
                        ], [
                            'is_favourite' => $hasFavouriteInAnyEstablishment,
                        ]);

                        if ($hasFavouriteInAnyEstablishment && !$eventShown->is_favourite) {
                            $eventShown->is_favourite = true;
                            $eventShown->save();
                        }
                    }
                }
            }
        });

        return response()->json(['message' => 'Művelet végrehajtva'], 200);
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
            return response()->json(['message' => 'nem jogosult'], 403);
        }


        $visibleEventIds = EventShown::where('establishment_id', $establishmentId)

                //SZAKKÖR MÓDOSÍTÁS
                ->where('user_id', $user->id)
                //
            ->distinct()
            ->pluck('event_id');

        $events = Event::whereIn('id', $visibleEventIds)
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

        $eventIds = $events
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $favouriteEventIdSet = collect();

        if (!empty($eventIds)) {
            $favouriteEventIdSet = EventShown::where('user_id', $user->id)
                ->whereIn('event_id', $eventIds)
                ->where('is_favourite', true)
                ->pluck('event_id')
                ->map(fn($id) => (int) $id)
                ->unique()
                ->flip();
        }

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

        $events = $events->map(function ($event) use ($feedbackStatsByEvent, $userFeedbackByEvent, $commentCountsByEvent, $favouriteEventIdSet) {
            $stats = $feedbackStatsByEvent->get($event->id);
            $commentStats = $commentCountsByEvent->get($event->id);

            $event->attending_count = (int) ($stats->attending_count ?? 0);
            $event->not_attending_count = (int) ($stats->not_attending_count ?? 0);
            $event->participant_count = (int) ($stats->attending_count ?? 0);
            $event->participants = (int) ($stats->attending_count ?? 0);
            $event->comment_count = (int) ($commentStats->comment_count ?? 0);
            $event->user_participation = $userFeedbackByEvent->get($event->id);
            $event->is_favourite = $favouriteEventIdSet->has((int) $event->id);

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

    //SZAKKÖR MÓDOSÍTÁS
    public function manageOccurrence(Request $request, int $eventId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Az esemény nem található.'], 404);
        }

        if ((int) $event->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Csak a létrehozó módosíthatja az alkalmat.'], 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:reschedule,cancel',
            'start_date' => 'nullable|required_if:action,reschedule|date',
            'end_date' => 'nullable|required_if:action,reschedule|date|after:start_date',
        ], [
            'action.required' => 'A művelet megadása kötelező.',
            'action.in' => 'A művelet csak reschedule vagy cancel lehet.',
            'start_date.required_if' => 'Áthelyezésnél új kezdési időpont szükséges.',
            'start_date.date' => 'A kezdési időpont érvénytelen.',
            'end_date.required_if' => 'Áthelyezésnél új befejezési időpont szükséges.',
            'end_date.date' => 'A befejezési időpont érvénytelen.',
            'end_date.after' => 'A befejezésnek későbbinek kell lennie, mint a kezdés.',
        ]);

        if ($validated['action'] === 'cancel') {
            $event->status = 'ended';
            $event->cancelled_at = now();
            $event->save();

            return response()->json([
                'message' => 'Az alkalom elmaradtként jelölve.',
            ]);
        }

        $event->start_date = $validated['start_date'];
        $event->end_date = $validated['end_date'];
        $event->status = 'upcoming';
        $event->cancelled_at = null;
        $event->save();

        return response()->json([
            'message' => 'Az alkalom időpontja sikeresen módosítva.',
            'event' => $event,
        ]);
    }

    public function makeFavourite(Request $request, int $eventId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Az esemény nem található.'], 404);
        }
        $eventShows = EventShown::where('event_id', $eventId)
            ->where('user_id', $user->id);

        if (!$eventShows->exists()) {
            return response()->json(['message' => 'nem jogosult'], 403);
        }

        $hasFavourite = EventShown::where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->where('is_favourite', true)
            ->exists();

        $newValue = !$hasFavourite;

        $eventShows->update([
            'is_favourite' => $newValue,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => $newValue
                ? 'Esemény hozzáadva a kedvencekhez.'
                : 'Esemény eltávolítva a kedvencek közül.',
        ]);
    }

}
//
