<?php

declare(strict_types=1);

use App\Modules\Festival\Presentation\Http\FestivalController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/npc/{npc}/festival')->group(function (): void {
    Route::get('/', [FestivalController::class, 'index'])->name('festival.index');
    Route::get('/rules', [FestivalController::class, 'rules'])->name('festival.rules');
    Route::post('/register', [FestivalController::class, 'register'])->middleware('throttle:30,1')->name('festival.register');
    Route::post('/deposit', [FestivalController::class, 'deposit'])->middleware('throttle:60,1')->name('festival.deposit');
    Route::post('/claim', [FestivalController::class, 'claim'])->middleware('throttle:30,1')->name('festival.claim');
    Route::post('/exchange', [FestivalController::class, 'exchange'])->middleware('throttle:60,1')->name('festival.exchange');
});
