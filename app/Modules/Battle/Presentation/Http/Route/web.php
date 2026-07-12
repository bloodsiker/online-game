<?php

declare(strict_types=1);

use App\Modules\Battle\Presentation\Http\FightController;
use App\Modules\Battle\Presentation\Http\FightListController;
use Illuminate\Support\Facades\Route;

Route::middleware(['updateLastOnline'])->group(function (): void {
    Route::get('/fights', [FightListController::class, 'index'])->name('fights');
    Route::get('/fight/log/{id}', [FightController::class, 'log'])->name('fight.log');
    Route::get('/fight/run-away/{id}', [FightController::class, 'runAway'])->name('fight.run-away');
    Route::get('/fight/attack/monster/{id}', [FightController::class, 'attackMonster'])->name('fight.attack.monster');
    Route::get('/fight/attack/{id}/{monsterId}/{action}', [FightController::class, 'attack'])->name('fight.attack');
    Route::get('/fight/{id}', [FightController::class, 'index'])->name('fight');
});
