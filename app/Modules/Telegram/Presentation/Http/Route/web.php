<?php

declare(strict_types=1);

use App\Modules\Telegram\Presentation\Http\TelegramSettingsController;
use Illuminate\Support\Facades\Route;

Route::post('/settings/telegram', [TelegramSettingsController::class, 'create'])
    ->middleware('throttle:6,1')->name('telegram.link');
Route::delete('/settings/telegram', [TelegramSettingsController::class, 'destroy'])
    ->middleware('throttle:6,1')->name('telegram.unlink');
