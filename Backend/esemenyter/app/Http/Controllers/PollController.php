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


        Poll::create([
            'question' => $request->question,
            'options' => $request->options,
            'establishment_id' => $request->establishment_id,
        ]);
    }
}
