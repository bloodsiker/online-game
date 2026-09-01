<?php

use App\Modules\Structure\Blacksmith\Presentation\Http\BlacksmithController;
use App\Modules\Structure\Blacksmith\Presentation\Http\GemController;
use App\Modules\Structure\Blacksmith\Presentation\Http\RuneController;
use Illuminate\Support\Facades\Route;

Route::get('/blacksmith/{id}/gems', [GemController::class, 'index'])->name('blacksmith.gems');
Route::post('/blacksmith/{id}/gems/insert', [GemController::class, 'insertGem'])->name('blacksmith.gems.insert');
Route::post('/blacksmith/{id}/gems/remove', [GemController::class, 'removeGem'])->name('blacksmith.gems.remove');
Route::post('/blacksmith/{id}/gems/open-socket', [GemController::class, 'openSocket'])->name('blacksmith.gems.open_socket');

Route::get('/blacksmith/{id}/runes', [RuneController::class, 'index'])->name('blacksmith.runes');
Route::post('/blacksmith/{id}/runes/imbue', [RuneController::class, 'imbue'])->name('blacksmith.runes.imbue');
Route::post('/blacksmith/{id}/runes/remove', [RuneController::class, 'removeRune'])->name('blacksmith.runes.remove');
Route::post('/blacksmith/{id}/runes/reroll', [RuneController::class, 'reroll'])->name('blacksmith.runes.reroll');
Route::post('/blacksmith/{id}/runes/open-slot', [RuneController::class, 'openSlot'])->name('blacksmith.runes.open_slot');

Route::get('/blacksmith/kraft/{id}', [BlacksmithController::class, 'kraftItem'])->name('blacksmith.kraft');
Route::get('/blacksmith/{id}/break', [BlacksmithController::class, 'breakItem'])->name('blacksmith.break');
Route::get('/blacksmith/{id}/upgrade', [BlacksmithController::class, 'upgrade'])->name('blacksmith.upgrade');
Route::post('/blacksmith/{id}/upgrade', [BlacksmithController::class, 'upgradeProcess'])->name('blacksmith.upgrade.process');
Route::get('/blacksmith/{id}/rarity-upgrade', [BlacksmithController::class, 'rarityUpgrade'])->name('blacksmith.rarity_upgrade');
Route::post('/blacksmith/{id}/rarity-upgrade', [BlacksmithController::class, 'rarityUpgradeProcess'])->name('blacksmith.rarity_upgrade.process');
Route::get('/blacksmith/{id}', [BlacksmithController::class, 'index'])->name('blacksmith');
