<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventShown;
use App\Models\EventRequest;
use Illuminate\Support\Carbon;
use App\Models\ClassModel;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{

    // Esemény létrehozása
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

            'is_recurring' => 'sometimes|boolean',
            'recurrence_frequency' => 'nullable|required_if:is_recurring,1|in:weekly',
            'recurrence_until' => 'nullable|required_if:is_recurring,1|date',

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

            'is_recurring.boolean' => 'Az ismétlődés mező értéke csak igaz vagy hamis lehet.',
            'recurrence_frequency.required_if' => 'Ismétlődő eseménynél meg kell adni a gyakoriságot.',
            'recurrence_frequency.in' => 'Jelenleg csak heti ismétlődés támogatott.',
            'recurrence_until.required_if' => 'Ismétlődő eseménynél záró dátum megadása kötelező.',
            'recurrence_until.date' => 'Az ismétlődés záró dátuma érvényes dátum legyen.',

            'users.array' => 'A users mezőnek tömbnek kell lennie.',
            'users.min' => 'Legalább :min felhasználót meg kell adni.',
            'users.*.integer' => 'A felhasználó azonosítónak számnak kell lennie.',
            'users.*.distinct' => 'A felhasználók között nem lehetnek ismétlődő értékek.',
            'users.*.exists' => 'A megadott felhasználó nem található.',
        ]);

        $isRecurring = false;
        if (array_key_exists('is_recurring', $validated)) {
            $isRecurring = $validated['is_recurring'];
        }

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

        // Az opcionális listáknak adunk alapértelmezett üres tömböt.
        $users = [];
        if (array_key_exists('users', $validated)) {
            $users = $validated['users'];
        }

        $collabIds = [];
        if (array_key_exists('collab_establishment_ids', $validated)) {
            $collabIds = $validated['collab_establishment_ids'];
        }

        $selectedClassSource = [];
        if (array_key_exists('selected_class_ids', $validated)) {
            $selectedClassSource = $validated['selected_class_ids'];
        }
        $selectedClassIds = array_values(array_map('intval', $selectedClassSource));

        $selectedGradeSource = [];
        if (array_key_exists('selected_grade_ids', $validated)) {
            $selectedGradeSource = $validated['selected_grade_ids'];
        }
        $selectedGradeIds = array_values(array_map('intval', $selectedGradeSource));

        $targetGroup = null;
        if (array_key_exists('target_group', $validated)) {
            $targetGroup = $validated['target_group'];
        }

        if ($targetGroup === 'osztaly_szintu' && !empty($selectedClassIds)) {
            $validClassIds = ClassModel::where('establishment_id', $validated['establishment_id'])
                ->whereIn('id', $selectedClassIds)
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();

            if (count($validClassIds) !== count($selectedClassIds)) {
                return response()->json([
                    'message' => 'Egy vagy több osztály nem tartozik az intézményhez!'
                ], 400);
            }
        }

        if ($targetGroup === 'evfolyam_szintu' && !empty($selectedGradeIds)) {
            $validGrades = ClassModel::where('establishment_id', $validated['establishment_id'])
                ->whereIn('grade', $selectedGradeIds)
                ->pluck('grade')
                ->map(function ($grade) {
                    return (int) $grade;
                })
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

            $event = DB::transaction(function () use ($user, $validated, $users, $isRecurring, $targetGroup) {
                if ($targetGroup === 'osztaly_szintu') {
                    $targetClassIdsSource = [];
                    if (array_key_exists('selected_class_ids', $validated)) {
                        $targetClassIdsSource = $validated['selected_class_ids'];
                    }
                    $targetClassIds = array_values(array_map('intval', $targetClassIdsSource));
                } else {
                    $targetClassIds = null;
                }

                if ($targetGroup === 'evfolyam_szintu') {
                    $targetGradeIdsSource = [];
                    if (array_key_exists('selected_grade_ids', $validated)) {
                        $targetGradeIdsSource = $validated['selected_grade_ids'];
                    }
                    $targetGradeIds = array_values(array_map('intval', $targetGradeIdsSource));
                } else {
                    $targetGradeIds = null;
                }

                if ($isRecurring) {
                    $recurrenceFrequency = 'weekly';
                    if (array_key_exists('recurrence_frequency', $validated)) {
                        $recurrenceFrequency = $validated['recurrence_frequency'];
                    }
                    $recurrenceUntil = $validated['recurrence_until'];
                } else {
                    $recurrenceFrequency = null;
                    $recurrenceUntil = null;
                }

                $event = Event::create([
                    'user_id' => $user->id,
                    'establishment_id' => $validated['establishment_id'],
                    'type' => $validated['type'],
                    'target_group' => $targetGroup,
                    'target_class_ids' => $targetClassIds,
                    'target_grade_ids' => $targetGradeIds,
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'content' => $validated['content'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],

                    'status' => 'upcoming',
                    'is_recurring' => $isRecurring,
                    'recurrence_frequency' => $recurrenceFrequency,
                    'recurrence_until' => $recurrenceUntil,
                    'recurrence_parent_event_id' => null,
                ]);

                $shownRows = [];

                // A létrehozó mindig lássa a saját eseményét.
                $shownRows[] = [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'establishment_id' => $validated['establishment_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // A kijelölt felhasználók számára is létrehozzuk a láthatóságot.
                foreach ($users as $userId) {
                    if ($userId == $user->id) {
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

                // Heti ismétlődő alkalmak létrehozása
                if ($isRecurring) {
                    $this->createWeeklyOccurrences(
                        $event,
                        $validated['recurrence_until'],
                        $shownRows
                    );
                }

                return $event;
            });
        }

        if ($validated['type'] == 'global') {
            if (!$this->isAdminEstablishment($user->id, $validated['establishment_id'])) {
                return response()->json(['message' => 'nem jogosult'], 403);
            }

            $event = DB::transaction(function () use ($user, $validated, $users, $collabIds, $targetGroup) {
                if ($targetGroup === 'osztaly_szintu') {
                    $targetClassIdsSource = [];
                    if (array_key_exists('selected_class_ids', $validated)) {
                        $targetClassIdsSource = $validated['selected_class_ids'];
                    }
                    $targetClassIds = array_values(array_map('intval', $targetClassIdsSource));
                } else {
                    $targetClassIds = null;
                }

                if ($targetGroup === 'evfolyam_szintu') {
                    $targetGradeIdsSource = [];
                    if (array_key_exists('selected_grade_ids', $validated)) {
                        $targetGradeIdsSource = $validated['selected_grade_ids'];
                    }
                    $targetGradeIds = array_values(array_map('intval', $targetGradeIdsSource));
                } else {
                    $targetGradeIds = null;
                }

                $event = Event::create([
                    'user_id' => $user->id,
                    'establishment_id' => $validated['establishment_id'],
                    'type' => $validated['type'],
                    'target_group' => $targetGroup,
                    'target_class_ids' => $targetClassIds,
                    'target_grade_ids' => $targetGradeIds,
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'content' => $validated['content'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],

                    'status' => 'upcoming',
                    'is_recurring' => false,
                    'recurrence_frequency' => null,
                    'recurrence_until' => null,
                    'recurrence_parent_event_id' => null,

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

    // Heti ismétlődő alkalmak automatikus generálása az első alkalom alapján
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

    // Kollaborációs esemény kérelem elfogadása vagy visszautasítása
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
                $users = [];
                if (array_key_exists('users', $validated)) {
                    $users = $validated['users'];
                }
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

    // Az intézményhez beérkezett kollaborációs esemény kérelmek listája
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
                $creatorName = 'Ismeretlen szervező';
                if (isset($creatorNames[$event->user_id])) {
                    $creatorName = $creatorNames[$event->user_id];
                }
                $event->creator_name = $creatorName;
            });
        }

        return response()->json([
            'events' => $events
        ]);
    }



    // Az intézmény eseményeinek listázása a bejelentkezett felhasználónak
    public function getEvents(Request $request, int $establishmentId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }
        if (!$this->isMemberEstablishment($user->id, $establishmentId)) {
            return response()->json(['message' => 'nem jogosult'], 403);
        }

        $this->recroactiveEventVisibility((int) $user->id, $establishmentId);


        $visibleEventIds = EventShown::where('establishment_id', $establishmentId)

            //SZAKKÖR MÓDOSÍTÁS
            ->where('user_id', $user->id)
            //
            ->distinct()
            ->pluck('event_id');

        $events = Event::where(function ($query) use ($visibleEventIds) {
            $query->whereIn('id', $visibleEventIds)
                ->orWhereIn('recurrence_parent_event_id', $visibleEventIds);
        })
            ->orderBy('start_date', 'asc')
            ->get();

        if ($events->isNotEmpty()) {
            $creatorNames = DB::table('users')
                ->whereIn('id', $events->pluck('user_id')->filter()->unique()->values())
                ->pluck('name', 'id');

            $events->each(function ($event) use ($creatorNames) {
                $creatorName = 'Ismeretlen szervező';
                if (isset($creatorNames[$event->user_id])) {
                    $creatorName = $creatorNames[$event->user_id];
                }
                $event->creator_name = $creatorName;
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
            $feedbackStatsByEvent = DB::table('event_shows')
                ->select(
                    'event_id',
                    DB::raw("SUM(CASE WHEN answer = 'y' THEN 1 ELSE 0 END) as attending_count"),
                    DB::raw("SUM(CASE WHEN answer = 'n' THEN 1 ELSE 0 END) as not_attending_count")
                )
                ->whereIn('event_id', $eventIds)
                ->groupBy('event_id')
                ->get()
                ->keyBy('event_id');

            $userFeedbackByEvent = DB::table('event_shows')
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

            $attendingCountValue = 0;
            if ($stats && isset($stats->attending_count)) {
                $attendingCountValue = (int) $stats->attending_count;
            }

            $notAttendingCountValue = 0;
            if ($stats && isset($stats->not_attending_count)) {
                $notAttendingCountValue = (int) $stats->not_attending_count;
            }

            $commentCountValue = 0;
            if ($commentStats && isset($commentStats->comment_count)) {
                $commentCountValue = (int) $commentStats->comment_count;
            }

            $event->attending_count = $attendingCountValue;
            $event->not_attending_count = $notAttendingCountValue;
            $event->participant_count = $attendingCountValue;
            $event->participants = $attendingCountValue;
            $event->comment_count = $commentCountValue;
            $event->user_participation = $userFeedbackByEvent->get($event->id);
            $event->is_favourite = $favouriteEventIdSet->has($event->id);

            return $event;
        });
        return response()->json([
            'events' => $events
        ]);
    }

    // Retroaktívan beállítja a láthatóságot azon eseményekre, amelyekre a felhasználó jogosult, de még nem szerepelt a listájában
    private function recroactiveEventVisibility(int $userId, int $establishmentId): void
    {
        $studentClassRows = DB::table('class_students')
            ->join('classes', 'class_students.class_id', '=', 'classes.id')
            ->where('class_students.user_id', $userId)
            ->where('classes.establishment_id', $establishmentId)
            ->get(['classes.id', 'classes.grade']);

        $homeroomClassRows = DB::table('classes')
            ->where('establishment_id', $establishmentId)
            ->where('user_id', $userId)
            ->get(['id', 'grade']);

        $classRows = $studentClassRows
            ->concat($homeroomClassRows)
            ->unique('id')
            ->values();

        $userClassIds = $classRows
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $userGradeIds = $classRows
            ->pluck('grade')
            ->map(fn($grade) => (int) $grade)
            ->unique()
            ->values()
            ->all();

        $existingVisibleEventIdSet = EventShown::where('establishment_id', $establishmentId)
            ->where('user_id', $userId)
            ->pluck('event_id')
            ->map(fn($eventId) => (int) $eventId)
            ->flip();

        $candidateEvents = Event::where('establishment_id', $establishmentId)
            ->whereIn('target_group', ['teljes_iskola', 'osztaly_szintu', 'sajat_osztaly', 'evfolyam_szintu', 'evfolyam'])
            ->get(['id', 'target_group', 'target_class_ids', 'target_grade_ids']);

        $rowsToInsert = [];

        foreach ($candidateEvents as $event) {
            if ($existingVisibleEventIdSet->has((int) $event->id)) {
                continue;
            }

            $isVisibleByTarget = false;

            if ($event->target_group === 'teljes_iskola') {
                $isVisibleByTarget = true;
            }

            if (in_array($event->target_group, ['osztaly_szintu', 'sajat_osztaly'], true)) {
                $targetClassIdsSource = [];
                if ($event->target_class_ids !== null) {
                    $targetClassIdsSource = $event->target_class_ids;
                }

                $eventClassIds = collect($targetClassIdsSource)
                    ->map(fn($id) => (int) $id)
                    ->values()
                    ->all();

                $isVisibleByTarget = !empty(array_intersect($eventClassIds, $userClassIds));
            }

            if (in_array($event->target_group, ['evfolyam_szintu', 'evfolyam'], true)) {
                $targetGradeIdsSource = [];
                if ($event->target_grade_ids !== null) {
                    $targetGradeIdsSource = $event->target_grade_ids;
                }

                $eventGradeIds = collect($targetGradeIdsSource)
                    ->map(fn($id) => (int) $id)
                    ->values()
                    ->all();

                $isVisibleByTarget = !empty(array_intersect($eventGradeIds, $userGradeIds));
            }

            if (!$isVisibleByTarget) {
                continue;
            }

            $rowsToInsert[] = [
                'event_id' => (int) $event->id,
                'user_id' => $userId,
                'establishment_id' => $establishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rowsToInsert)) {
            EventShown::insert($rowsToInsert);
        }
    }

    // Felhasználó részvételi válaszának mentése
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

        DB::table('event_shows')->updateOrInsert(
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

        $attendingCount = DB::table('event_shows')
            ->where('event_id', $eventId)
            ->where('answer', 'y')
            ->count();

        $notAttendingCount = DB::table('event_shows')
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

    // Esemény aktív résztvevőinek listázása
    public function getParticipants(Request $request, int $eventId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Az esemény nem található.'], 404);
        }

        if ($event->user_id != $user->id) {
            return response()->json(['message' => 'Csak a létrehozó láthatja a résztvevőlistát.'], 403);
        }

        $participants = DB::table('event_shows')
            ->join('users', 'event_shows.user_id', '=', 'users.id')
            ->leftJoin('students', function ($join) use ($event) {
                $join->on('students.user_id', '=', 'users.id')
                    ->where('students.establishment_id', '=', $event->establishment_id);
            })
            ->leftJoin('staffs', function ($join) use ($event) {
                $join->on('staffs.user_id', '=', 'users.id')
                    ->where('staffs.establishment_id', '=', $event->establishment_id);
            })
            ->where('event_shows.event_id', $eventId)
            ->where('event_shows.answer', 'y')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'event_shows.answer',
                'event_shows.updated_at',
                DB::raw('COALESCE(students.alias, staffs.alias) as alias')
            )
            ->orderBy('users.name')
            ->get();

        return response()->json([
            'participants' => $participants,
        ]);
    }

    // Résztvevő kitiltása az eseményből
    public function banParticipant(Request $request, int $eventId, int $userId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Az esemény nem található.'], 404);
        }

        if ($event->user_id != $user->id) {
            return response()->json(['message' => 'Csak a létrehozó tilthat ki résztvevőt.'], 403);
        }

        if ($userId == $event->user_id) {
            return response()->json(['message' => 'A létrehozó nem tiltható ki az eseményből.'], 422);
        }

        $wasParticipant = DB::table('event_shows')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->where('answer', 'y')
            ->exists();

        if (!$wasParticipant) {
            return response()->json(['message' => 'A felhasználó nem aktív résztvevő.'], 404);
        }

        DB::table('event_shows')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->delete();

        DB::table('event_messages')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->delete();

        $attendingCount = DB::table('event_shows')
            ->where('event_id', $eventId)
            ->where('answer', 'y')
            ->count();

        $notAttendingCount = DB::table('event_shows')
            ->where('event_id', $eventId)
            ->where('answer', 'n')
            ->count();

        $commentCount = DB::table('event_messages')
            ->where('event_id', $eventId)
            ->count();

        return response()->json([
            'message' => 'A résztvevő kitiltása sikeres volt.',
            'attending_count' => $attendingCount,
            'not_attending_count' => $notAttendingCount,
            'comment_count' => $commentCount,
        ]);
    }

    // Chat engedélyezése vagy letiltása az eseményen
    public function handleChat(Request $request, int $eventId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'nem jogosult'], 401);
        }
        $validated = $request->validate([
            'chat_enabled' => 'required|boolean',
        ], [
            'chat_enabled.required' => 'A chat engedélyezése kötelező.',
            'chat_enabled.boolean' => 'A chat engedélyezése mező értéke csak igaz vagy hamis lehet.',
        ]);

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Az esemény nem található.'], 404);
        }
        if ($event->user_id !== $user->id) {
            return response()->json(['message' => 'Csak a létrehozó kezelheti a chat beállításait.'], 403);
        }
        $event->chat_enabled = $validated['chat_enabled'];
        $event->save();

        return response()->json([
            'message' => 'Chat beállítások frissítve.',
            'chat_enabled' => $event->chat_enabled,
            'event' => $event->id,
        ]);
    }

    // Ismétlődő esemény egy alkalmának módosítása vagy elmaradtként jelölése
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

        if ($event->user_id != $user->id) {
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

    // Kedvenc jelölés be- és kikapcsolása az eseményen
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

        $message = $newValue
            ? 'Esemény hozzáadva a kedvencekhez.'
            : 'Esemény eltávolítva a kedvencek közül.';

        return response()->json([
            'message' => $message,
        ]);
    }
}
