<?php

use App\Modules\Structure\ClanBoss\Presentation\Http\ClanBossController;
use Illuminate\Support\Facades\Route;

Route::get('/clan-boss/{id}', [ClanBossController::class, 'index'])->name('clan_boss');
Route::post('/clan-boss/{id}/open', [ClanBossController::class, 'open'])->name('clan_boss.open');
Route::post('/clan-boss/{id}/attack', [ClanBossController::class, 'attack'])->name('clan_boss.attack');
