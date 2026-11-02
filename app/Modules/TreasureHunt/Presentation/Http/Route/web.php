<?php

use App\Modules\TreasureHunt\Presentation\Http\TreasureHuntController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'prison.context', 'throttle:60,1'])->prefix('treasure')->name('treasure.')->group(function (): void {
    Route::post('/maps/{item}/check', [TreasureHuntController::class, 'check'])->name('check');
    Route::post('/fragments/{item}/assemble', [TreasureHuntController::class, 'assemble'])->name('assemble');
    Route::post('/{hunt}/start', [TreasureHuntController::class, 'start'])->name('start');
    Route::post('/{hunt}/complete', [TreasureHuntController::class, 'complete'])->name('complete');
    Route::post('/{hunt}/cancel', [TreasureHuntController::class, 'cancel'])->name('cancel');
});
