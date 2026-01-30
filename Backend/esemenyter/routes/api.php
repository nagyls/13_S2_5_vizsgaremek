<?php
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\UserLogoutController;
use App\Http\Controllers\Auth\UserRegisterController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\EventController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\EstablishmentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [UserRegisterController::class, 'register']);

Route::post('/login', [UserAuthController::class, 'login']);
Route::post('/logout', [UserLogoutController::class, 'logout'])->middleware('auth:sanctum');

Route::prefix('regions')->group(function () {
    Route::get('/all', [RegionController::class, 'getallregions']); // összes régió
    Route::get('/{id}', [RegionController::class, 'showregion']); // id alapu keresés
    Route::get('/', [RegionController::class, 'regions']); // keresés
});
Route::prefix('innerregions')->group(function () {
    Route::get('/all', [RegionController::class, 'getallinnerregions']); 
    Route::get('/', [RegionController::class, 'innerregions']); 
});
Route::prefix('settlements')->group(function () {
    Route::get('/all', [RegionController::class, 'getallsettlements']); 
    Route::get('/', [RegionController::class, 'settlements']); 
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/event', [EventController::class, 'store']);
    Route::post('/establishment', [EstablishmentController::class, 'store']);
});