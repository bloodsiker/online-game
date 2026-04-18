<?php

use App\Modules\Location\Presentation\Http\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['updateLastOnline'])->group(function () {
    Route::get('/location/move/{direction}', [LocationController::class, 'moveTo'])->name('move-to');
    Route::get('/location', [LocationController::class, 'index'])->name('location');
});

Route::get('/take_items', [LocationController::class, 'takeItems'])->name('take_items');
