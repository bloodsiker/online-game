<?php

use App\Http\Controllers\Admin\ActionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\MonsterController;
use App\Http\Controllers\Admin\NpcController;
use App\Http\Controllers\Admin\RaceController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\StructureController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::match(['GET', 'POST'], '/item/{item}', [ItemController::class, 'info'])->name('item.info');
Route::post('/item/recipe/{recipe}', [ItemController::class, 'addItemToRecipe'])->name('item.recipe.add_item');
Route::get('/item/recipe/{recipe}/delete/{item}', [ItemController::class, 'deleteItemInRecipe'])->name('item.recipe.delete_item');
Route::get('/items', [ItemController::class, 'list'])->name('items');

Route::match(['GET', 'POST'], '/monster/create', [MonsterController::class, 'create'])->name('monster.create');
Route::get('/monster/{monster}/drop/delete/{item}', [MonsterController::class, 'infoDropDeleteItem'])->name('monster.info.drop.delete_item');
Route::get('/monster/{monster}/delete/{location}', [MonsterController::class, 'deleteLocation'])->name('monster.info.delete_location');
Route::match(['GET', 'POST'], '/monster/{monster}/location', [MonsterController::class, 'location'])->name('monster.info.location');
Route::match(['GET', 'POST'], '/monster/{monster}/drop', [MonsterController::class, 'infoDrop'])->name('monster.info.drop');
Route::match(['GET', 'POST'], '/monster/{monster}', [MonsterController::class, 'info'])->name('monster.info');
Route::get('/monsters', [MonsterController::class, 'list'])->name('monsters');

Route::get('/structure/{structure}/shop/delete_item/{item}', [StructureController::class, 'infoShopDeleteItem'])->name('structure.info.shop_delete_item');
Route::match(['GET', 'POST'], '/structure/{structure}/shop', [StructureController::class, 'infoShop'])->name('structure.info.shop');
Route::get('/structure/{structure}/action/{action}', [StructureController::class, 'infoActionDelete'])->name('structure.info.action_delete');
Route::match(['GET', 'POST'], '/structure/{structure}/action', [StructureController::class, 'infoAction'])->name('structure.info.action');
Route::match(['GET', 'POST'], '/structure/{structure}', [StructureController::class, 'info'])->name('structure.info');
Route::get('/structures', [StructureController::class, 'list'])->name('structures');

Route::match(['GET', 'POST'], '/map/create', [MapController::class, 'create'])->name('map.create');
Route::match(['GET', 'POST'],'/map/{map}/locations', [MapController::class, 'location'])->name('map.location');
Route::match(['GET', 'POST'],'/map/{map}', [MapController::class, 'info'])->name('map.info');
Route::get('/maps', [MapController::class, 'list'])->name('maps');

Route::match(['GET', 'POST'],'/location/{location}', [LocationController::class, 'info'])->name('location.info');
Route::get('/locations', [LocationController::class, 'list'])->name('locations');

Route::match(['GET', 'POST'], '/action/{action}', [ActionController::class, 'info'])->name('action.info');
Route::get('/action', [ActionController::class, 'list'])->name('action');

Route::match(['GET', 'POST'],'/race/{race}', [RaceController::class, 'info'])->name('race.info');
Route::get('/races', [RaceController::class, 'list'])->name('race');

Route::match(['GET', 'POST'], '/npc/create', [NpcController::class, 'create'])->name('npc.create');
Route::get('/npc/list', [NpcController::class, 'list'])->name('npc');
Route::match(['GET', 'POST'],'/npc/{npc}', [NpcController::class, 'info'])->name('npc.info');

Route::match(['GET', 'POST'], '/skill/create', [SkillController::class, 'create'])->name('skill.create');
Route::match(['GET', 'POST'],'/skill/{skill}', [SkillController::class, 'info'])->name('skill.info');
Route::get('/skills', [SkillController::class, 'list'])->name('skills');

Route::get('/users', [UserController::class, 'index'])->name('users');
