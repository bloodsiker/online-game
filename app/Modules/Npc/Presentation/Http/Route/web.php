<?php

declare(strict_types=1);

use App\Modules\Npc\Presentation\Http\NpcController;
use Illuminate\Support\Facades\Route;

Route::get('/info/npc/{id}', [NpcController::class, 'info'])->name('info.npc');
Route::get('/npc/{id}/dialogue/{node?}', [NpcController::class, 'dialogue'])->name('npc.dialogue');
Route::get('/npc/{id}', [NpcController::class, 'index'])->name('npc');
