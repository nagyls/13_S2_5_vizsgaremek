<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
//use App\Http\Controllers\EventController;
//use App\Http\Controllers\EstablishmentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//Route::post('/events', [EventController::class, 'store']);
//Route::post('/establishments', [EstablishmentController::class, 'store']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
