<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// *************************************************************************
// RUTA DE ADMINISTRACIÓN: Creación de usuario por un usuario autenticado (Jefe).
// Usa solo el middleware 'auth'. La validación de rol ('jefe') se hace
// dentro del controlador si es necesaria, o a través de un middleware 'jefe' 
// explícito si se añade en el futuro, pero sin causar errores ahora.
// *************************************************************************
Route::post('users', [RegisteredUserController::class, 'store'])
    ->middleware('auth') 
    ->name('user.store');


// --- GRUPO GUEST (Solo para usuarios no autenticados: login, registro inicial) ---
Route::middleware('guest')->group(function () {
    
    // Ruta GET /register, que redirige al login
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    // Mantenemos la ruta POST /register sin nombre, para evitar conflictos.
    Route::post('register', [RegisteredUserController::class, 'store']);


    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// --- GRUPO AUTH (Para usuarios autenticados) ---
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});