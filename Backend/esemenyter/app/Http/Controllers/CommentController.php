<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventMessage;
use App\Models\EventShown;
use App\Models\Event;

class CommentController extends Controller
{
    // Esemény kommentjeinek lapozható lekérdezése
    public function getComments(Request $request, $eventId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $eventview = EventShown::where('event_id', $eventId)->where('user_id', $user->id)->first();
        if (!$eventview || $eventview->answer !== 'y' || !$event->chat_enabled) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        // A lekérdezés méretét korlátozzuk, hogy ne legyen túl nagy.
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
    // Új komment hozzáadása egy eseményhez
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
        $event = Event::find($request->event_id);
        if (!$event) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $eventview = EventShown::where('event_id', $request->event_id)->where('user_id', $user->id)->first();
        if (!$eventview || $eventview->answer !== 'y' || !$event->chat_enabled) {
            return response()->json(['error' => 'Hozzáférés megtagadva'], 403);
        }

        $comment = EventMessage::create([
            'event_id' => $request->event_id,
            'user_id' => $user->id,
            'content' => $request->input('content'),
        ]);
        return response()->json($comment, 201);
    }

    // Komment törlése
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

        $event = Event::find($comment->event_id);
        if (!$event) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $canDeleteOwnComment = false;
        if ($comment->user_id == $user->id) {
            $canDeleteOwnComment = true;
        }

        $canDeleteAsCreator = false;
        if ($event->user_id == $user->id) {
            $canDeleteAsCreator = true;
        }

        // Saját kommentet lehet törölni, vagy az esemény létrehozója törölhet.
        if (!$canDeleteOwnComment && !$canDeleteAsCreator) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Komment törölve.'], 200);
    }
}
