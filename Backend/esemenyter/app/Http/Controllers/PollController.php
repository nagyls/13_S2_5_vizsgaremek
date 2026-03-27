<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollAnswer;
use App\Models\Establishment;
use App\Models\Event;
use App\Models\EventShown;

class PollController extends Controller
{
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
            'event_id' => 'required|exists:establishments,id',
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
        ]);

        $event = Establishment::find($validated['event_id']);
        if (!$event || $event->user_id !== $user->id) {
            return response()->json(['message' => 'nem jogosult!'], 403);
        }
        $poll = Poll::create([
            'title' => $validated['title'],
            'user_id' => $user->id,
            'event_id' => $validated['event_id'],
        ]);
        $insertData = array_map(function ($option) use ($poll) {
            return [
                'option' => $option,
                'poll_id' => $poll->id,
            ];
        }, $validated['options']);
        PollOption::insert($insertData);
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

        $validated = $request->validate([
            'option_id' => 'required|exists:poll_options,id',
        ], [
            'option_id.required' => 'Az opció azonosító mező kötelező.',
            'option_id.exists' => 'Az opció azonosító nem létezik.',
        ]);

        $option = PollOption::find($validated['option_id']);
        if (PollAnswer::where('poll_option_id', $option->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'már szavaztál!'], 400);
        }
        if ($option->poll_id !== $poll->id) {
            return response()->json(['message' => 'opció nem tartozik a szavazáshoz!'], 400);
        }

        PollAnswer::create([
            'poll_option_id' => $option->id,
            'user_id' => $user->id,
        ]);
        return response()->json(['message' => 'szavazat rögzítve!'], 200);
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
        $results = $poll->options->map(function ($option) {
            return [
                'option' => $option->option,
                'votes' => $option->answers->count(),
            ];
        });
        return response()->json([
            'poll' => $poll->title,
            'results' => $results,
        ], 200);
    }
}
