<?php

use App\Modules\Location\Presentation\Http\LocationController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::get('/maps', [LocationController::class, 'maps'])->name('maps');
    Route::get('/location/move/{direction}', [LocationController::class, 'moveTo'])->name('move-to');
    Route::get('/location/gate/{gateId}', [LocationController::class, 'passGate'])->name('gate-pass');
    Route::get('/location', [LocationController::class, 'index'])->name('location');
});

Route::get('/take_items', [LocationController::class, 'takeItems'])->name('take_items');
