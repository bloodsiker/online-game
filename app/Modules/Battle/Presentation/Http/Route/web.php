<?php

declare(strict_types=1);

use App\Modules\Battle\Presentation\Http\FightController;
use App\Modules\Battle\Presentation\Http\FightListController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'prison.context'])->group(function (): void {
    Route::get('/fights', [FightListController::class, 'index'])->name('fights');
    Route::get('/fight/log/{id}', [FightController::class, 'log'])->name('fight.log');
    Route::get('/fight/{id}', [FightController::class, 'index'])->name('fight');
    Route::get('/clan-boss/battle/{id}', [FightController::class, 'clanBoss'])->name('fight.clan-boss');
    Route::get('/clan-boss/battle/{id}/attack/{monsterId}/{action}', [FightController::class, 'attackClanBoss'])
        ->middleware('throttle:120,1')
        ->name('fight.clan-boss.attack');
    Route::post('/fight/{id}/leave-clan-boss', [FightController::class, 'leaveClanBoss'])
        ->middleware('throttle:30,1')
        ->name('fight.leave-clan-boss');

    // Мутації стану бою: throttle проти автоклікерів (клієнтський кулдаун обходиться).
    Route::get('/fight/run-away/{id}', [FightController::class, 'runAway'])
        ->middleware('throttle:120,1')
        ->name('fight.run-away');
    Route::get('/fight/attack/monster/{id}', [FightController::class, 'attackMonster'])
        ->middleware('throttle:120,1')
        ->name('fight.attack.monster');
    Route::get('/fight/attack/{id}/{monsterId}/{action}', [FightController::class, 'attack'])
        ->middleware('throttle:120,1')
        ->name('fight.attack');
});
