<?php

declare(strict_types=1);

use App\Modules\Dungeon\Presentation\Http\DungeonController;
use Illuminate\Support\Facades\Route;

Route::get('/dungeons', [DungeonController::class, 'index'])->name('dungeon.index');
Route::get('/dungeon/{id}', [DungeonController::class, 'show'])->name('dungeon.show');
Route::post('/dungeon/{id}/enter', [DungeonController::class, 'enter'])->name('dungeon.enter');
Route::post('/dungeon/exit', [DungeonController::class, 'exit'])->name('dungeon.exit');
