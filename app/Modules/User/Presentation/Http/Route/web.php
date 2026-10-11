<?php

declare(strict_types=1);

use App\Modules\User\Presentation\Http\GameSettingsController;
use App\Modules\User\Presentation\Http\UserController;
use App\Modules\User\Presentation\Http\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/settings', [GameSettingsController::class, 'show'])->middleware('auth')->name('settings');
Route::post('/settings', [GameSettingsController::class, 'update'])->middleware(['auth', 'throttle:30,1'])->name('settings.update');
Route::get('/info/u/{id}', [UserController::class, 'info'])->name('info.user');
Route::get('/settings/profile', [UserProfileController::class, 'show'])->middleware('auth')->name('settings.profile');
Route::post('/settings/profile', [UserProfileController::class, 'update'])->middleware(['auth', 'throttle:10,1'])->name('settings.profile.update');
