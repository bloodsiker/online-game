<?php

declare(strict_types=1);

use App\Modules\Auth\Presentation\Http\ChangePasswordController;
use App\Modules\Auth\Presentation\Http\EmailVerificationController;
use App\Modules\Auth\Presentation\Http\LoginController;
use App\Modules\Auth\Presentation\Http\RegisterController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login'])
    ->name('login')
    ->withoutMiddleware([VerifyCsrfToken::class]);
Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])
    ->name('register')
    ->withoutMiddleware([VerifyCsrfToken::class]);
Route::get('/register/check', [RegisterController::class, 'registerCheck'])->name('register.check');
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
    ->middleware('auth')
    ->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::get('/email/verified', [EmailVerificationController::class, 'complete'])
    ->name('verification.complete');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.resend');
Route::post('/settings/password', ChangePasswordController::class)
    ->middleware(['auth', 'throttle:5,1'])
    ->name('settings.password.update');
