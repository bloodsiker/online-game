<?php

use App\Modules\Event\Presentation\Http\EventController;
use App\Modules\Event\Presentation\Http\WorldEventFavoriteController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/events', [EventController::class, 'index'])->name('events');
    Route::get('/events/state', [EventController::class, 'state'])->name('events.state');
    Route::post('/events/{event}/favorite', WorldEventFavoriteController::class)->name('events.favorite');
});
