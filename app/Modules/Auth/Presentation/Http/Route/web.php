<?php

declare(strict_types=1);

use App\Modules\Auth\Presentation\Http\ChangePasswordController;
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
Route::post('/register/check', [RegisterController::class, 'registerCheck'])->name('register.check');
Route::post('/settings/password', ChangePasswordController::class)
    ->middleware(['auth', 'throttle:5,1'])
    ->name('settings.password.update');
