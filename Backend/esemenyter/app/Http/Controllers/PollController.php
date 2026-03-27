<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollAnswer;


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
        $event = $request->event_id;
        if ($event->user_id !== $user->id) {
            return response()->json(['message' => 'nem jogosult!'], 403);
        }
        Poll::create([
            'title' => $request->question,
            'user_id' => $user->id,
            'event_id' => $request->event_id,
        ]);
        PollOption::insert(array_map(function ($option) {
            return [
                'option' => $option,
                'poll_id' => Poll::latest()->first()->id,
            ];
        }, $request->options));
    }
}
