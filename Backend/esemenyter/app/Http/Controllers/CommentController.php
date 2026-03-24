<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventMessage;
use App\Models\EventShown;

class CommentController extends Controller
{
    //kommentek lekérése
    public function getComments(Request $request, $eventId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $eventview = EventShown::where('event_id', $eventId)->where('user_id', $user->id)->first();
        if (!$eventview || $eventview->answer !== 'y') {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $perPage = (int) $request->query('per_page', 50);
        if ($perPage <= 0) {
            $perPage = 50;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }

        $comments = EventMessage::where('event_id', $eventId)
            ->with('user')
            ->paginate($perPage);

        return response()->json($comments);
    }
    //komment hozzáadása
    public function makeComment(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'content' => 'required|string|max:1000',
        ], [
            'event_id.required' => 'Az event_id mező kötelező.',
            'event_id.exists' => 'Az event_id mezőnek egy létező esemény ID-nek kell lennie.',
            'content.required' => 'A content mező kötelező.',
            'content.string' => 'A content mezőnek szöveges értéknek kell lennie.',
            'content.max' => 'A content mező nem lehet hosszabb 1000 karakternél.',
        ]);

        $eventview = EventShown::where('event_id', $request->event_id)->where('user_id', $user->id)->first();
        if (!$eventview || $eventview->answer !== 'y') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $comment = EventMessage::create([
            'event_id' => $request->event_id,
            'user_id' => $user->id,
            'content' => $request->input('content'),
        ]);
        return response()->json($comment, 201);
    }

    //komment törlése
    public function deleteComment(Request $request, $commentId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $comment = EventMessage::find($commentId);
        if (!$comment) {
            return response()->json(['error' => 'Not found'], 404);
        }
        if ($comment->user_id !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Komment törölve.'], 200);
    }
}
