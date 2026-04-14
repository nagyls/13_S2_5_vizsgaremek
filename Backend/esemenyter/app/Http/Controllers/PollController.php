<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollAnswer;
use App\Models\Event;
use App\Models\EventShown;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function getEventPoll(Request $request, $eventId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'nem található!'], 404);
        }

        $eventview = EventShown::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        $isCreator = (int) $event->user_id === (int) $user->id;
        $hasOptedIn = $eventview && $eventview->answer === 'y';

        if (!$isCreator && !$hasOptedIn) {
            return response()->json(['message' => 'nem jogosult!'], 403);
        }

        $polls = Poll::with('options.answers')
            ->where('event_id', $event->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'polls' => $polls->map(function ($poll) use ($user, $isCreator, $hasOptedIn) {
                return $this->transformPoll($poll, $user->id, $isCreator, $hasOptedIn);
            })->values(),
            'can_create' => $isCreator,
        ], 200);
    }

    public function makePoll(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'options' => 'required|array|min:2|max:10',
            'options.*' => 'required|string|max:255',
            'event_id' => 'required|exists:events,id',
            'deadline' => 'nullable|date',
            'is_timed' => 'required|boolean',
            'hidden_results' => 'required|boolean',
        ], [
            'title.required' => 'A kérdés mező kötelező.',
            'title.string' => 'A kérdés mezőnek szöveges értéknek kell lennie.',
            'title.max' => 'A kérdés mező nem lehet hosszabb 255 karakternél.',
            'options.required' => 'Az opciók mező kötelező.',
            'options.array' => 'Az opciók mezőnek tömbnek kell lennie.',
            'options.min' => 'Legalább 2 opció szükséges.',
            'options.max' => 'Legfeljebb 10 opció megengedett.',
            'options.*.required' => 'Minden opciónak kötelező értéket kell megadni.',
            'options.*.string' => 'Minden opciónak szöveges értéknek kell lennie.',
            'options.*.max' => 'Minden opciónak nem lehet hosszabb 255 karakternél.',
            'event_id.required' => 'Az esemény azonosító mező kötelező.',
            'event_id.exists' => 'Az esemény azonosító nem létezik.',
            'deadline.date' => 'A lezárási dátum érvénytelen.',
            'is_timed.required' => 'Az időzített jelző kötelező.',
            'is_timed.boolean' => 'Az időzített jelző értéke hibás.',
            'hidden_results.required' => 'Az eredmény-elrejtési jelző kötelező.',
            'hidden_results.boolean' => 'Az eredmény-elrejtési jelző értéke hibás.',
        ]);

        $event = Event::find($validated['event_id']);
        if (!$event || $event->user_id !== $user->id) {
            return response()->json(['message' => 'nem jogosult!'], 403);
        }

        if ($validated['is_timed'] && empty($validated['deadline'])) {
            return response()->json(['message' => 'Időzített szavazásnál a lezárási dátum kötelező.'], 422);
        }

        if (!empty($validated['deadline']) && $validated['deadline'] < now()->toDateString()) {
            return response()->json(['message' => 'A lezárási dátum nem lehet korábbi a szavazás létrehozásának napjánál.'], 422);
        }

        $uniqueOptions = collect($validated['options'])
            ->map(function ($option) {
                return trim($option);
            })
            ->filter(function ($option) {
                return $option !== '';
            })
            ->unique()
            ->values();

        if ($uniqueOptions->count() < 2) {
            return response()->json(['message' => 'Legalább 2 különböző opció szükséges.'], 422);
        }

        DB::transaction(function () use ($user, $validated, $uniqueOptions) {
            if ($validated['is_timed']) {
                $deadline = $validated['deadline'] ?? null;
            } else {
                $deadline = null;
            }

            $poll = Poll::create([
                'title' => $validated['title'],
                'user_id' => $user->id,
                'event_id' => $validated['event_id'],
                'start_date' => now()->toDateString(),
                'deadline' => $deadline,
                'is_timed' => $validated['is_timed'],
                'hidden_results' => $validated['hidden_results'],
                'is_active' => true,
            ]);

            $insertData = $uniqueOptions->map(function ($option) use ($poll) {
                return [
                    'title' => $option,
                    'poll_id' => $poll->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            PollOption::insert($insertData);
        });

        return response()->json(['message' => 'szavazás létrehozva!'], 201);
    }

    public function answerPoll(Request $request, $pollId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }
        $poll = Poll::find($pollId);
        if (!$poll) {
            return response()->json(['message' => 'nem található!'], 404);
        }
        $eventview = EventShown::where('event_id', $poll->event_id)->where('user_id', $user->id)->first();
        if (!$eventview || $eventview->answer !== 'y') {
            return response()->json(['message' => 'nem jogosult!'], 403);
        }

        if (!$poll->hasStarted()) {
            return response()->json(['message' => 'A szavazás még nem indult el.'], 422);
        }

        if ($poll->hasEnded()) {
            return response()->json(['message' => 'A szavazás már lezárult.'], 422);
        }

        $validated = $request->validate([
            'option_id' => 'required|exists:poll_options,id',
        ], [
            'option_id.required' => 'Az opció azonosító mező kötelező.',
            'option_id.exists' => 'Az opció azonosító nem létezik.',
        ]);

        $option = PollOption::find($validated['option_id']);

        if ($option->poll_id !== $poll->id) {
            return response()->json(['message' => 'opció nem tartozik a szavazáshoz!'], 400);
        }

        if (PollAnswer::where('poll_id', $poll->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'már szavaztál!'], 400);
        }

        PollAnswer::create([
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => $user->id,
        ]);
        return response()->json(['message' => 'szavazat rögzítve!'], 200);
    }

    public function stopPoll(Request $request, $pollId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }

        $poll = Poll::with('event')->find($pollId);
        if (!$poll) {
            return response()->json(['message' => 'nem található!'], 404);
        }

        if ((int) $poll->event->user_id !== (int) $user->id) {
            return response()->json(['message' => 'nem jogosult!'], 403);
        }

        if ($poll->hasEnded()) {
            return response()->json(['message' => 'A szavazás már lezárult.'], 422);
        }

        $poll->is_active = false;
        $poll->save();

        return response()->json(['message' => 'A szavazás lezárva.'], 200);
    }

    public function getPollResults(Request $request, $pollId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'nem felhatalmazott!'], 401);
        }

        $poll = Poll::with('options.answers')->find($pollId);
        if (!$poll) {
            return response()->json(['message' => 'nem található!'], 404);
        }

        $event = Event::find($poll->event_id);
        $isCreator = $event && (int) $event->user_id === (int) $user->id;

        $eventview = EventShown::where('event_id', $poll->event_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$isCreator && (!$eventview || $eventview->answer !== 'y')) {
            return response()->json(['message' => 'nem jogosult!'], 403);
        }

        if (!$poll->resultsAreVisible()) {
            return response()->json(['message' => 'Az eredmények csak a szavazás lezárása után láthatók.'], 403);
        }

        $results = $this->buildResults($poll);
        $totalVotes = (int) $results->sum('votes');

        return response()->json([
            'poll_id' => $poll->id,
            'poll' => $poll->title,
            'results' => $results->values(),
            'total_votes' => $totalVotes,
        ], 200);
    }

    protected function transformPoll(Poll $poll, int $userId, bool $isCreator, bool $hasOptedIn): array
    {
        $userAnswer = PollAnswer::where('poll_id', $poll->id)
            ->where('user_id', $userId)
            ->first();

        $results = $this->buildResults($poll);
        $totalVotes = (int) $results->sum('votes');
        $resultsVisible = $poll->resultsAreVisible();

        if ($resultsVisible) {
            $totalVotesResult = $totalVotes;
        } else {
            $totalVotesResult = null;
        }

        if ($userAnswer) {
            $selectedOptionId = (int) $userAnswer->poll_option_id;
        } else {
            $selectedOptionId = null;
        }

        return [
            'id' => $poll->id,
            'title' => $poll->title,
            'event_id' => $poll->event_id,
            'start_date' => optional($poll->start_date)->toDateString(),
            'deadline' => optional($poll->deadline)->toDateString(),
            'is_timed' => $poll->is_timed,
            'hidden_results' => $poll->hidden_results,
            'is_active' => $poll->is_active,
            'created_at' => optional($poll->created_at)->toIso8601String(),
            'is_started' => $poll->hasStarted(),
            'is_ended' => $poll->hasEnded(),
            'results_visible' => $resultsVisible,
            'total_votes' => $totalVotesResult,
            'has_answered' => (bool) $userAnswer,
            'selected_option_id' => $selectedOptionId,
            'can_answer' => $hasOptedIn && $poll->hasStarted() && !$poll->hasEnded() && !$userAnswer,
            'can_manage' => $isCreator,
            'options' => $poll->options->map(function ($option) use ($resultsVisible, $results) {
                $result = $results->firstWhere('option_id', $option->id);

                if ($resultsVisible) {
                    $votes = $result['votes'] ?? 0;
                    $percentage = $result['percentage'] ?? 0;
                } else {
                    $votes = null;
                    $percentage = null;
                }

                return [
                    'id' => $option->id,
                    'title' => $option->title,
                    'votes' => $votes,
                    'percentage' => $percentage,
                ];
            })->values(),
        ];
    }

    protected function buildResults(Poll $poll)
    {
        $baseResults = $poll->options->map(function ($option) {
            return [
                'option_id' => $option->id,
                'option' => $option->title,
                'votes' => $option->answers->count(),
            ];
        })->values();

        $totalVotes = (int) $baseResults->sum('votes');

        return $baseResults->map(function ($item) use ($totalVotes) {
            if ($totalVotes > 0) {
                $percentage = round(($item['votes'] / $totalVotes) * 100, 1);
            } else {
                $percentage = 0;
            }

            return [
                'option_id' => $item['option_id'],
                'option' => $item['option'],
                'votes' => $item['votes'],
                'percentage' => $percentage,
            ];
        })->values();
    }
}
