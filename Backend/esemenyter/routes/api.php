<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', function (Request $request) {

    $request->validate([
        'username' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6'
    ]);

    DB::table('users')->insert([
        'name' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'created_at' => now(),
        'updated_at' => now()
    ]);

    return response()->json(['message' => 'Sikeres regisztráció!']);
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string'
    ]);

    // Felhasználó lekérése username alapján
    $user = DB::table('users')->where('name', $request->username)->first();

    if (!$user) {
        return response()->json(['message' => 'Hibás felhasználónév!'], 401);
    }

    // Jelszó ellenőrzés
    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Hibás jelszó!'], 401);
    }

    return response()->json([
        'message' => 'Sikeres bejelentkezés!',
        'user' => $user
    ]);
});
Route::post('/events', function (Request $request) {

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
});