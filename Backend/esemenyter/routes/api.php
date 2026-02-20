<?php

use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\UserLogoutController;
use App\Http\Controllers\Auth\UserRegisterController;
use App\Http\Controllers\Auth\VerificationController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\EventController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;

use App\Http\Controllers\RequestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [UserRegisterController::class, 'register']);

Route::post('/login', [UserAuthController::class, 'login']);
Route::post('/logout', [UserLogoutController::class, 'logout'])->middleware('auth:sanctum');

// Email verifikáció
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])
        ->middleware(['throttle:6,1'])
        ->name('verification.resend');
    Route::get('/email/verification-status', [VerificationController::class, 'check'])
        ->name('verification.check');
});

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



Route::prefix('establishments')->group(function () {
    Route::get('/{id}', [EstablishmentController::class, 'getEstablishmentbyId']); // id alapu keresés
    Route::get('/', [EstablishmentController::class, 'getEstablishments']); // keresés
    // Route::post('/', [EstablishmentController::class, 'store']);   // uj
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/events', [EventController::class, 'getEvents']);
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);

    Route::post('/establishment', [EstablishmentController::class, 'store']);
    Route::get('/establishment/mine', [EstablishmentController::class, 'getMyEstablishments']);

    Route::post('/classes', [ClassController::class, 'store']);
    Route::get('/classes/{establishment}', [ClassController::class, 'getClasses']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/requests/student/{establishment}', [RequestController::class, 'getStudentRequests']);
    Route::get('/requests/teacher/{establishment}', [RequestController::class, 'getTeacherRequests']);
    Route::post('/requests', [RequestController::class, 'submitRequest']);
    Route::post('/requests/handle', [RequestController::class, 'handleRequest']);
    Route::post('/requests/revoke', [RequestController::class, 'requestRevoke']);
});

Route::prefix('members')->group(function () {
    Route::get('/students/{establishment}', [StudentController::class, 'getStudents']);
    Route::get('/staff/{establishment}', [StaffController::class, 'getStaffs']);
});
