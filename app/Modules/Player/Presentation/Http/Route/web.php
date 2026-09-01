<?php

use App\Modules\Player\Presentation\Http\CharacterController;
use Illuminate\Support\Facades\Route;

Route::get('/character', [CharacterController::class, 'index'])->name('character');
Route::get('/character/professions', [CharacterController::class, 'professions'])->name('character.professions');
Route::get('/character/points', [CharacterController::class, 'point'])->name('character.point');
Route::post('/character/points-save', [CharacterController::class, 'pointSave'])->name('character.point_save');
