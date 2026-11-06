<?php

declare(strict_types=1);

use App\Modules\Telegram\Presentation\Http\Middleware\AuthenticateTelegramApi;
use App\Modules\Telegram\Presentation\Http\Middleware\AuthenticateTelegramMiniApp;
use App\Modules\Telegram\Presentation\Http\Middleware\AuthenticateTelegramWebhook;
use App\Modules\Telegram\Presentation\Http\TelegramApiController;
use App\Modules\Telegram\Presentation\Http\TelegramBackpackPreviewController;
use App\Modules\Telegram\Presentation\Http\TelegramChatController;
use App\Modules\Telegram\Presentation\Http\TelegramMiniAppController;
use App\Modules\Telegram\Presentation\Http\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', TelegramWebhookController::class)
    ->middleware([AuthenticateTelegramWebhook::class, 'throttle:telegram-webhook'])->name('telegram.webhook');

Route::get('/telegram/backpack', [TelegramMiniAppController::class, 'show'])->name('telegram.backpack.app');
Route::prefix('api/v1/telegram/mini-app')->name('telegram.mini-app.')
    ->middleware([AuthenticateTelegramMiniApp::class, 'throttle:telegram-mini-app'])->group(function (): void {
        Route::get('/backpack', [TelegramMiniAppController::class, 'backpack'])->name('backpack');
        Route::get('/character', [TelegramMiniAppController::class, 'character'])->name('character');
        Route::get('/auction-lots', [TelegramMiniAppController::class, 'auction'])->name('auction');
    });

Route::prefix('api/v1/telegram/mini-app/chat')->name('telegram.mini-app.chat.')
    ->middleware(AuthenticateTelegramMiniApp::class)->group(function (): void {
        Route::get('/messages', [TelegramChatController::class, 'messages'])->middleware('throttle:telegram-chat-read')->name('messages');
        Route::post('/send', [TelegramChatController::class, 'send'])->middleware('throttle:telegram-chat-send')->name('send');
        Route::post('/broadcasting/auth', [TelegramChatController::class, 'authorize'])->middleware('throttle:telegram-chat-auth')->name('auth');
    });

Route::get('/telegram/backpack/preview/{token}', TelegramBackpackPreviewController::class)
    ->where('token', '[a-f0-9]{48}')->middleware(['signed', 'throttle:120,1'])->name('telegram.backpack.preview');

Route::prefix('api/v1/telegram')->name('telegram.api.')
    ->middleware([AuthenticateTelegramApi::class, 'throttle:telegram-api'])->group(function (): void {
        Route::get('/character', [TelegramApiController::class, 'character'])->name('character');
        Route::get('/backpack', [TelegramApiController::class, 'backpack'])->name('backpack');
        Route::get('/auction-lots', [TelegramApiController::class, 'auction'])->name('auction');
        Route::get('/exchange-orders', [TelegramApiController::class, 'exchange'])->name('exchange');
    });
