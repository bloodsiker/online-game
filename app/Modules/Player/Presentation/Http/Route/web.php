<?php

use App\Modules\Player\Presentation\Http\CharacterController;
use App\Modules\Player\Presentation\Http\HealingController;
use App\Modules\Player\Presentation\Http\HotbarController;
use App\Modules\Player\Presentation\Http\SlotsController;
use Illuminate\Support\Facades\Route;

Route::get('/character', [CharacterController::class, 'index'])->name('character');
Route::get('/character/professions', [CharacterController::class, 'professions'])->name('character.professions');
Route::get('/character/influence', [CharacterController::class, 'influence'])->name('character.influence');
Route::get('/character/influence/{map}', [CharacterController::class, 'influenceDetails'])
    ->whereNumber('map')
    ->name('character.influence.show');
Route::get('/character/points', [CharacterController::class, 'point'])->name('character.point');
Route::post('/character/points-save', [CharacterController::class, 'pointSave'])->name('character.point_save');
Route::get('/heal/{id}', [HealingController::class, 'index'])->name('heal');
Route::middleware('auth')->group(function (): void {
    Route::get('/slots', [SlotsController::class, 'index'])->name('slots');
    Route::post('/slots/update', [SlotsController::class, 'update'])->name('slots.update');
    Route::get('/hotbar', [HotbarController::class, 'index'])->name('hotbar.index');
    Route::post('/hotbar/set', [HotbarController::class, 'set'])->name('hotbar.set');
    Route::post('/hotbar/use', [HotbarController::class, 'use'])->name('hotbar.use');
    Route::delete('/hotbar/clear/{slot}', [HotbarController::class, 'clear'])->name('hotbar.clear');
});
