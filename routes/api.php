<?php

use App\Http\Controllers\API\V1\Auth\AuthenticationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider or bootstrap/app.php
| within a group which is assigned the "api" middleware group.
|
*/

Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthenticationController::class, 'register'])->name('api.auth.register');
    Route::post('/verify', [AuthenticationController::class, 'verify'])->name('api.auth.verify_otp');
    Route::post('/login', [AuthenticationController::class, 'login'])->name('api.auth.login');
});
