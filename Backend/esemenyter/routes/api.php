<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/events', [EventController::class, 'store']);

Route::prefix('regions')->group(function () {
    Route::get('/', [RegionController::class, 'regions']); // With search
    Route::get('/all', [RegionController::class, 'getallregions']); // All regions
    Route::get('/{id}', [RegionController::class, 'region']); // Single region
});
Route::prefix('subregions')->group(function () {
    Route::get('/', [RegionController::class, 'subregions']); 
    Route::get('/all', [RegionController::class, 'getallinnerregions']); 
});