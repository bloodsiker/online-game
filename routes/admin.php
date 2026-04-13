<?php

use App\Http\Controllers\Admin\ActionController;
use App\Http\Controllers\Admin\DocsController;
use App\Http\Controllers\Admin\ClanController;
use App\Http\Controllers\Admin\QuestController;
use App\Http\Controllers\Admin\ReputationController;
use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\MonsterController;
use App\Http\Controllers\Admin\NpcController;
use App\Http\Controllers\Admin\RaceController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\StructureController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/docs/dungeon', [DocsController::class, 'dungeon'])->name('docs.dungeon');
Route::get('/docs/clan', [DocsController::class, 'clan'])->name('docs.clan');

Route::get('/referral/stages', [ReferralController::class, 'stages'])->name('referral.stages');
Route::post('/referral/stages', [ReferralController::class, 'storeStage'])->name('referral.stage.store');
Route::get('/referral/stages/{stage}/delete', [ReferralController::class, 'deleteStage'])->name('referral.stage.delete');
Route::get('/referral/stats', [ReferralController::class, 'stats'])->name('referral.stats');

Route::get('/api/locations', [ApiController::class, 'locations'])->name('api.locations');
Route::get('/api/items', [ApiController::class, 'items'])->name('api.items');
Route::get('/api/npcs', [ApiController::class, 'npcs'])->name('api.npcs');
Route::get('/api/quests', [ApiController::class, 'quests'])->name('api.quests');
Route::get('/api/maps', [ApiController::class, 'maps'])->name('api.maps');

Route::get('/items', [ItemController::class, 'list'])->name('items');
Route::get('/item/create', [ItemController::class, 'create'])->name('item.create');
Route::post('/item/create', [ItemController::class, 'store'])->name('item.store');
Route::match(['GET', 'POST'], '/item/{item}', [ItemController::class, 'info'])->name('item.info');
Route::post('/item/recipe/{recipe}/update', [ItemController::class, 'updateRecipe'])->name('item.recipe.update');
Route::post('/item/recipe/{recipe}', [ItemController::class, 'addItemToRecipe'])->name('item.recipe.add_item');
Route::get('/item/recipe/{recipe}/delete/{item}', [ItemController::class, 'deleteItemInRecipe'])->name('item.recipe.delete_item');
Route::post('/item/{item}/stat', [ItemController::class, 'addStat'])->name('item.stat.add');
Route::get('/item/{item}/stat/{stat}/delete', [ItemController::class, 'deleteStat'])->name('item.stat.delete');
Route::post('/item/{item}/effect', [ItemController::class, 'addEffect'])->name('item.effect.add');
Route::get('/item/{item}/effect/{effect}/delete', [ItemController::class, 'deleteEffect'])->name('item.effect.delete');
Route::post('/item/{item}/requirement', [ItemController::class, 'addRequirement'])->name('item.requirement.add');
Route::get('/item/{item}/requirement/{requirement}/delete', [ItemController::class, 'deleteRequirement'])->name('item.requirement.delete');

Route::match(['GET', 'POST'], '/monster/create', [MonsterController::class, 'create'])->name('monster.create');
Route::post('/monster/{monster}/drop', [MonsterController::class, 'infoDrop'])->name('monster.info.drop');
Route::get('/monster/{monster}/drop/delete/{item}', [MonsterController::class, 'infoDropDeleteItem'])->name('monster.info.drop.delete_item');
Route::get('/monster/{monster}/locations', [MonsterController::class, 'infoLocation'])->name('monster.info.location');
Route::post('/monster/{monster}/location', [MonsterController::class, 'location'])->name('monster.info.location.save');
Route::post('/monster/{monster}/location/{location}/aggression', [MonsterController::class, 'updateLocation'])->name('monster.info.location.aggression');
Route::get('/monster/{monster}/delete/{location}', [MonsterController::class, 'deleteLocation'])->name('monster.info.delete_location');
Route::post('/monster/{monster}/phase', [MonsterController::class, 'addPhase'])->name('monster.boss.phase.add');
Route::post('/monster/{monster}/phase/{phase}', [MonsterController::class, 'updatePhase'])->name('monster.boss.phase.update');
Route::get('/monster/{monster}/phase/{phase}/delete', [MonsterController::class, 'deletePhase'])->name('monster.boss.phase.delete');
Route::post('/monster/{monster}/mechanic', [MonsterController::class, 'addMechanic'])->name('monster.boss.mechanic.add');
Route::post('/monster/{monster}/mechanic/{mechanic}', [MonsterController::class, 'updateMechanic'])->name('monster.boss.mechanic.update');
Route::get('/monster/{monster}/mechanic/{mechanic}/delete', [MonsterController::class, 'deleteMechanic'])->name('monster.boss.mechanic.delete');
Route::get('/monster/{monster}/mechanic/{mechanic}/toggle', [MonsterController::class, 'toggleMechanic'])->name('monster.boss.mechanic.toggle');
Route::match(['GET', 'POST'], '/monster/{monster}', [MonsterController::class, 'info'])->name('monster.info');
Route::get('/monsters', [MonsterController::class, 'list'])->name('monsters');

Route::get('/structure/{structure}/shop/delete_item/{item}', [StructureController::class, 'infoShopDeleteItem'])->name('structure.info.shop_delete_item');
Route::post('/structure/{structure}/shop', [StructureController::class, 'infoShop'])->name('structure.info.shop');
Route::get('/structure/{structure}/action/{action}', [StructureController::class, 'infoActionDelete'])->name('structure.info.action_delete');
Route::post('/structure/{structure}/action', [StructureController::class, 'infoAction'])->name('structure.info.action');
Route::match(['GET', 'POST'], '/structure/{structure}', [StructureController::class, 'info'])->name('structure.info');
Route::get('/structures', [StructureController::class, 'list'])->name('structures');

Route::match(['GET', 'POST'], '/map/create', [MapController::class, 'create'])->name('map.create');
Route::post('/map/{map}/locations', [MapController::class, 'location'])->name('map.location');
Route::match(['GET', 'POST'],'/map/{map}', [MapController::class, 'info'])->name('map.info');
Route::get('/maps', [MapController::class, 'list'])->name('maps');

Route::match(['GET', 'POST'], '/location/create', [LocationController::class, 'create'])->name('location.create');
Route::match(['GET', 'POST'],'/location/{location}', [LocationController::class, 'info'])->name('location.info');
Route::get('/locations', [LocationController::class, 'list'])->name('locations');

Route::match(['GET', 'POST'], '/action/create', [ActionController::class, 'create'])->name('action.create');
Route::match(['GET', 'POST'], '/action/{action}', [ActionController::class, 'info'])->name('action.info');
Route::get('/action', [ActionController::class, 'list'])->name('action');

Route::match(['GET', 'POST'], '/race/create', [RaceController::class, 'create'])->name('race.create');
Route::match(['GET', 'POST'],'/race/{race}', [RaceController::class, 'info'])->name('race.info');
Route::get('/races', [RaceController::class, 'list'])->name('race');

Route::match(['GET', 'POST'], '/npc/create', [NpcController::class, 'create'])->name('npc.create');
Route::get('/npc/list', [NpcController::class, 'list'])->name('npc');
Route::match(['GET', 'POST'],'/npc/{npc}', [NpcController::class, 'info'])->name('npc.info');

Route::match(['GET', 'POST'], '/skill/create', [SkillController::class, 'create'])->name('skill.create');
Route::match(['GET', 'POST'],'/skill/{skill}', [SkillController::class, 'info'])->name('skill.info');
Route::get('/skills', [SkillController::class, 'list'])->name('skills');

Route::get('/quests', [QuestController::class, 'list'])->name('quests');
Route::match(['GET', 'POST'], '/quest/create', [QuestController::class, 'create'])->name('quest.create');
Route::match(['GET', 'POST'], '/quest/{quest}', [QuestController::class, 'info'])->name('quest.info');
Route::post('/quest/{quest}/objective', [QuestController::class, 'addObjective'])->name('quest.objective.add');
Route::get('/quest/{quest}/objective/{objective}/delete', [QuestController::class, 'deleteObjective'])->name('quest.objective.delete');
Route::post('/quest/{quest}/reward', [QuestController::class, 'addReward'])->name('quest.reward.add');
Route::get('/quest/{quest}/reward/{reward}/delete', [QuestController::class, 'deleteReward'])->name('quest.reward.delete');

Route::get('/clans', [ClanController::class, 'list'])->name('clans');
Route::get('/clan/{clan}', [ClanController::class, 'info'])->name('clan.info');

Route::get('/reputations', [ReputationController::class, 'list'])->name('reputations');
Route::match(['GET', 'POST'], '/reputation/create', [ReputationController::class, 'create'])->name('reputation.create');
Route::match(['GET', 'POST'], '/reputation/{reputation}', [ReputationController::class, 'info'])->name('reputation.info');
Route::post('/reputation/{reputation}/tier', [ReputationController::class, 'addTier'])->name('reputation.tier.add');
Route::get('/reputation/{reputation}/tier/{tier}/delete', [ReputationController::class, 'deleteTier'])->name('reputation.tier.delete');
Route::post('/reputation/{reputation}/tier/{tier}/quest', [ReputationController::class, 'addTierQuest'])->name('reputation.tier.quest.add');
Route::get('/reputation/{reputation}/tier/{tier}/quest/{tierQuest}/delete', [ReputationController::class, 'deleteTierQuest'])->name('reputation.tier.quest.delete');
Route::post('/reputation/{reputation}/shop', [ReputationController::class, 'addShopItem'])->name('reputation.shop.add');
Route::get('/reputation/{reputation}/shop/{shopItem}/delete', [ReputationController::class, 'deleteShopItem'])->name('reputation.shop.delete');

Route::get('/users', [UserController::class, 'index'])->name('users');

Route::get('/players', [PlayerController::class, 'index'])->name('players');
Route::match(['GET', 'POST'], '/player/{player}', [PlayerController::class, 'info'])->name('player.info');
Route::post('/player/{player}/backpack/add', [PlayerController::class, 'backpackAdd'])->name('player.backpack.add');
Route::get('/player/{player}/backpack/{backpack}/delete', [PlayerController::class, 'backpackDelete'])->name('player.backpack.delete');
