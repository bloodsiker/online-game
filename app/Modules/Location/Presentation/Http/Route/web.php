<?php

use App\Modules\Location\Presentation\Http\LocationController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::get('/maps', [LocationController::class, 'maps'])->name('maps');
    Route::get('/location/move/{direction}', [LocationController::class, 'moveTo'])->name('move-to');
    Route::get('/location/gate/{gateId}', [LocationController::class, 'passGate'])->name('gate-pass');
    Route::get('/location', [LocationController::class, 'index'])->name('location');
    Route::get('/gathering', [LocationController::class, 'gathering'])->name('gathering');
    Route::get('/gathering/state', [LocationController::class, 'gatheringState'])->name('gathering.state');
    Route::post('/gathering/node/{node}/start', [LocationController::class, 'startGathering'])->name('gathering.start');
    Route::post('/gathering/complete', [LocationController::class, 'completeGathering'])->name('gathering.complete');
    Route::post('/gathering/cancel', [LocationController::class, 'cancelGathering'])->name('gathering.cancel');
});

Route::get('/take_items', [LocationController::class, 'takeItems'])->name('take_items');
