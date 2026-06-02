<?php

use App\Http\Controllers\AuthController;
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
});
