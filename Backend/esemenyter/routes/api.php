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
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PollController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [UserRegisterController::class, 'register']);

Route::post('/login', [UserAuthController::class, 'login']);
Route::delete('/logout', [UserLogoutController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('verification.verify');

// Email verifikáció
Route::middleware('auth:sanctum')->group(function () {
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
    Route::patch('/user/update', [UserAuthController::class, 'update']);
    //esemény kezelés
    Route::get('/establishment/{establishmentId}/events', [EventController::class, 'getEvents']);
    Route::post('/establishment/events', [EventController::class, 'store']);
    Route::patch('/events/{eventId}/participation', [EventController::class, 'setParticipation']);
    Route::patch('/events/{eventId}/favourite', [EventController::class, 'makeFavourite']);
    Route::patch('/events/{eventId}/occurrence', [EventController::class, 'manageOccurrence']);  //SZAKKÖR MÓDOSÍTÁS
    Route::patch('/events/{eventId}/chat', [EventController::class, 'handleChat']);  //CHAT MÓDOSÍTÁS
    Route::get('/events/{eventId}/poll', [PollController::class, 'getEventPoll']);

    //intézmény kezelés
    Route::prefix('establishment')->group(function () {
        Route::post('/create', [EstablishmentController::class, 'store']); //intézmény létrehozása
        Route::patch('/switch', [EstablishmentController::class, 'switchEstablishment']);
        Route::get('{establishmentId}/role', [EstablishmentController::class, 'getRole']); // felhasználó szerepének lekérdezése az aktuális intézményben
        Route::get('/mine', [EstablishmentController::class, 'getMyEstablishments']); //összes intézmény ahol a user tag
        Route::get('/{establishmentId}', [EstablishmentController::class, 'getEstablishmentbyId']); // id alapu keresés

        Route::get('{establishmentId}/members/students', [MemberController::class, 'getStudents']);
        Route::get('{establishmentId}/members/staff', [MemberController::class, 'getStaff']);
        //kollaborácios események
        Route::get('/{establishmentId}/event-access', [EventController::class, 'getCollabEvents']);
        Route::patch('/{establishmentId}/event-access', [EventController::class, 'handleCollabEvents']);
    });

    //osztály kezelés
    Route::get('/establishment/{establishmentId}/classes/members', [ClassController::class, 'getClassMemmbersInMass']);
    Route::get('/establishment/{establishmentId}/grades', [ClassController::class, 'getEstablishmentGrades']);
    Route::get('/establishment/{establishmentId}/grades/members', [ClassController::class, 'getGradeMembersInMass']);

    Route::get('/establishment/{establishmentId}/classes', [ClassController::class, 'getClasses']);
    Route::get('/establishment/{establishmentId}/classes/{classId}', [ClassController::class, 'getClassMembers']);
    Route::delete('/establishment/{establishmentId}/classes/{classId}', [ClassController::class, 'deleteClass']);
    Route::post('/establishment/classes/create', [ClassController::class, 'store']);
    //álnév kezelés
    Route::patch('/establishment/{establishmentId}/members/{memberId}/alias', [MemberController::class, 'setAlias']);
    // modify class membership -> PATCH
    Route::patch('/establishment/classes/add-students', [MemberController::class, 'storeInClass']);
    Route::patch('/establishment/classes/remove-students', [MemberController::class, 'removeFromClass']);
    Route::patch('/establishment/{establishmentId}/classes/{classId}', [ClassController::class, 'updateClassTeacher']);

    //kérelmek
    Route::get('/establishment/requests/my-pending', [RequestController::class, 'getMyPendingRequest']);
    Route::get('/establishment/{establishmentId}/requests/students', [RequestController::class, 'getStudentRequests']);
    Route::get('/establishment/{establishmentId}/requests/teachers', [RequestController::class, 'getTeacherRequests']);
    Route::get('/establishment/{establishmentId}/requests/me', [RequestController::class, 'getMyRequestStatus']);

    Route::post('/establishment/requests/create', [RequestController::class, 'submitRequest']);
    Route::patch('/establishment/requests/handle', [RequestController::class, 'handleRequest']);

    Route::delete('/establishment/{establishmentId}/requests/revoke', [RequestController::class, 'revokeRequest']);

    // kommentek
    Route::get('/events/{eventId}/comments', [CommentController::class, 'getComments']);
    Route::post('/events/comments', [CommentController::class, 'makeComment']);
    Route::delete('/events/comments/{commentId}', [CommentController::class, 'deleteComment']);

    // szavazások
    Route::post('/polls', [PollController::class, 'makePoll']);
    Route::post('/polls/{pollId}/answer', [PollController::class, 'answerPoll']);
    Route::patch('/polls/{pollId}/close', [PollController::class, 'stopPoll']);
    Route::get('/polls/{pollId}/results', [PollController::class, 'getPollResults']);
});
