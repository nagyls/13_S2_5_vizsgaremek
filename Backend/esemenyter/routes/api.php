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
use App\Http\Controllers\CommentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [UserRegisterController::class, 'register']);

Route::post('/login', [UserAuthController::class, 'login']);
Route::delete('/logout', [UserLogoutController::class, 'logout'])->middleware('auth:sanctum');

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

// Keresési és lekérdezési végpontok(helyek, intézmények)
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
    Route::get('/', [EstablishmentController::class, 'getEstablishments']); // keresés
});



Route::middleware('auth:sanctum')->group(function () {
    //esemény kezelés
    Route::get('/establishment/{establishmentId}/events', [EventController::class, 'getEvents']);
    Route::post('/establishment/events', [EventController::class, 'store']);
    Route::patch('/events/{eventId}/participation', [EventController::class, 'setParticipation']);
    Route::patch('/events/{eventId}/favourite', [EventController::class, 'makeFavourite']);
    Route::patch('/events/{eventId}/occurrence', [EventController::class, 'manageOccurrence']);  //SZAKKÖR MÓDOSÍTÁS
    
    Route::get('/establishment/{establishmentId}/event-access', [EventController::class, 'getCollabEvents']);
    Route::patch('/establishment/{establishmentId}/event-access', [EventController::class, 'handleCollabEvents']);
    //intézmény kezelés
    Route::get('/establishment/role', [EstablishmentController::class, 'getRole']);
    Route::post('/establishment/create', [EstablishmentController::class, 'store']);
    Route::get('/establishment/mine', [EstablishmentController::class, 'getMyEstablishments']);
    Route::get('/establishment/{establishmentId}', [EstablishmentController::class, 'getEstablishmentbyId']); // id alapu keresés
    //osztály kezelés
    Route::get('/establishment/{establishmentId}/classes/members', [ClassController::class, 'getClassMemmbersInMass']);
    Route::get('/establishment/{establishmentId}/grades', [ClassController::class, 'getEstablishmentGrades']);
    Route::get('/establishment/{establishmentId}/grades/members', [ClassController::class, 'getGradeMembersInMass']);
    
    Route::get('/establishment/{establishmentId}/classes', [ClassController::class, 'getClasses']);
    Route::get('/establishment/{establishmentId}/classes/{classId}', [ClassController::class, 'getClassMembers']);
    Route::delete('/establishment/{establishmentId}/classes/{classId}', [ClassController::class, 'deleteClass']);
    Route::post('/establishment/classes/create', [ClassController::class, 'store']);
    // modify class membership -> PATCH
    Route::patch('/establishment/classes/add-students', [StudentController::class, 'storeInClass']);
    Route::patch('/establishment/classes/remove-students', [StudentController::class, 'removeFromClass']);
    Route::patch('/establishment/{establishmentId}/classes/{classId}', [ClassController::class, 'updateClassTeacher']);
    //kérelmek
    Route::get('/establishment/{establishmentId}/requests/students', [RequestController::class, 'getStudentRequests']);
    Route::get('/establishment/{establishmentId}/requests/teachers', [RequestController::class, 'getTeacherRequests']);
    
    Route::post('/establishment/requests/create', [RequestController::class, 'submitRequest']);
    Route::patch('/establishment/requests/handle', [RequestController::class, 'handleRequest']);

    Route::delete('/establishment/{establishmentId}/requests/revoke', [RequestController::class, 'revokeRequest']);

    // kommentek
    Route::get('/events/{eventId}/comments', [CommentController::class, 'getComments']);
    Route::post('/events/comments', [CommentController::class, 'makeComment']);
    Route::delete('/events/comments/{commentId}', [CommentController::class, 'deleteComment']);
});

Route::prefix('members')->group(function () {
    Route::get('/students/{establishmentId}', [StudentController::class, 'getStudents']);
    Route::get('/staff/{establishmentId}', [StaffController::class, 'getStaff']);
});
