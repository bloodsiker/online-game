<?php

declare(strict_types=1);

use App\Modules\Telegram\Presentation\Http\Middleware\AuthenticateTelegramApi;
use App\Modules\Telegram\Presentation\Http\Middleware\AuthenticateTelegramWebhook;
use App\Modules\Telegram\Presentation\Http\TelegramApiController;
use App\Modules\Telegram\Presentation\Http\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', TelegramWebhookController::class)
    ->middleware([AuthenticateTelegramWebhook::class, 'throttle:telegram-webhook'])->name('telegram.webhook');

Route::prefix('api/v1/telegram')->name('telegram.api.')
    ->middleware([AuthenticateTelegramApi::class, 'throttle:telegram-api'])->group(function (): void {
        Route::get('/character', [TelegramApiController::class, 'character'])->name('character');
        Route::get('/auction-lots', [TelegramApiController::class, 'auction'])->name('auction');
        Route::get('/exchange-orders', [TelegramApiController::class, 'exchange'])->name('exchange');
    });
