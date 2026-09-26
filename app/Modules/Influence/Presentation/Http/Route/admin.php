<?php

declare(strict_types=1);

use App\Modules\Influence\Presentation\Http\Admin\InfluenceController;
use Illuminate\Support\Facades\Route;

Route::get('/influence', [InfluenceController::class, 'index'])->name('influence.index');
Route::get('/influence/{map}', [InfluenceController::class, 'show'])->name('influence.show');
Route::post('/influence/{map}/levels', [InfluenceController::class, 'storeLevel'])->name('influence.level.store');
Route::put('/influence/{map}/levels/{level}', [InfluenceController::class, 'updateLevel'])->name('influence.level.update');
Route::delete('/influence/{map}/levels/{level}', [InfluenceController::class, 'destroyLevel'])->name('influence.level.destroy');
Route::post('/influence/{map}/medals', [InfluenceController::class, 'storeMedal'])->name('influence.medal.store');
Route::put('/influence/{map}/medals/{medal}', [InfluenceController::class, 'updateMedal'])->name('influence.medal.update');
Route::delete('/influence/{map}/medals/{medal}', [InfluenceController::class, 'destroyMedal'])->name('influence.medal.destroy');
Route::post('/influence/{map}/medals/{medal}/stats', [InfluenceController::class, 'storeMedalStat'])->name('influence.medal.stat.store');
Route::delete('/influence/{map}/medals/{medal}/stats/{stat}', [InfluenceController::class, 'destroyMedalStat'])->name('influence.medal.stat.destroy');
Route::post('/influence/{map}/levels/{level}/bonuses', [InfluenceController::class, 'storeBonus'])->name('influence.bonus.store');
Route::delete('/influence/{map}/levels/{level}/bonuses/{bonus}', [InfluenceController::class, 'destroyBonus'])->name('influence.bonus.destroy');
Route::post('/influence/{map}/levels/{level}/rewards', [InfluenceController::class, 'storeReward'])->name('influence.reward.store');
Route::delete('/influence/{map}/levels/{level}/rewards/{reward}', [InfluenceController::class, 'destroyReward'])->name('influence.reward.destroy');
Route::post('/influence/{map}/requirements', [InfluenceController::class, 'storeRequirement'])->name('influence.requirement.store');
Route::delete('/influence/{map}/requirements/{type}/{requirement}', [InfluenceController::class, 'destroyRequirement'])->name('influence.requirement.destroy');
Route::post('/influence/{map}/shop-sections', [InfluenceController::class, 'storeShopSection'])->name('influence.shop.section.store');
Route::delete('/influence/{map}/shop-sections/{section}', [InfluenceController::class, 'destroyShopSection'])->name('influence.shop.section.destroy');
Route::post('/influence/{map}/shop-sections/{section}/items', [InfluenceController::class, 'storeShopItem'])->name('influence.shop.item.store');
Route::delete('/influence/{map}/shop-sections/{section}/items/{item}', [InfluenceController::class, 'destroyShopItem'])->name('influence.shop.item.destroy');
