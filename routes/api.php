<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketClassifierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — OAuth Passport
|--------------------------------------------------------------------------
*/

// Public routes (rate limited)
Route::prefix('auth')->group(function () {
    Route::post('register',        [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login',           [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,5');
    Route::post('reset-password',  [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
});

// Protected routes (requires valid Passport Bearer token)
Route::middleware('auth:api')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('user',         [AuthController::class, 'user']);

    // Profile
    Route::get('profile',              [ProfileController::class, 'show'])->name('api.profile.show');
    Route::patch('profile',            [ProfileController::class, 'update'])->name('api.profile.update');
    Route::put('profile/password',     [ProfileController::class, 'updatePassword'])->name('api.profile.password');

    //classfier
    Route::post('tickets/classify', [TicketClassifierController::class, 'classify'])
    ->middleware('throttle:20,1');
});
