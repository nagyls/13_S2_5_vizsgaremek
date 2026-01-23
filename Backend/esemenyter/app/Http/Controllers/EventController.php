<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    //
    public function event_create(Request $request)
    {
         $request->validate([
            'type'        => 'required|in:local,global',
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content'     => 'nullable|string',
            'users_id'    => 'required|exists:users,id',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'in:open,closed'
        ]);

        $eventId = DB::table('events')->insertGetId([
            'type'        => $request->type,
            'title'       => $request->title,
            'description' => $request->description,
            'content'     => $request->content,
            'users_id'    => $request->users_id,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'status'      => $request->status ?? 'open',
            'created_at'  => now(),
        ]);
        return response()->json([
            'message' => 'Event created successfully',
            'event_id' => $eventId
        ], );
       
    }
    
}
