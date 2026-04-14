<?php

use App\Modules\Clan\Presentation\Http\ClanController;
use App\Modules\Clan\Presentation\Http\ClanSkillController;
use App\Modules\Clan\Presentation\Http\ClanWarehouseController;
use App\Modules\Clan\Presentation\Http\ClanTreasuryController;
use Illuminate\Support\Facades\Route;

Route::get('/clan/member', [ClanController::class, 'member'])->name('clan.member');
Route::post('/clan/member/save-roles', [ClanController::class, 'saveMemberRoles'])->name('clan.member.save-roles');
Route::post('/clan/member/leave', [ClanController::class, 'leaveClan'])->name('clan.member.leave');
Route::delete('/clan/member/{target}', [ClanController::class, 'kickMember'])->name('clan.member.kick');
Route::get('/clan/role', [ClanController::class, 'role'])->name('clan.role');
Route::post('/clan/role/add', [ClanController::class, 'addRole'])->name('clan.role.add');
Route::post('/clan/role/save', [ClanController::class, 'saveRoles'])->name('clan.role.save');
Route::delete('/clan/role/{role}', [ClanController::class, 'deleteRole'])->name('clan.role.delete');
Route::post('/clan/invite', [ClanController::class, 'invite'])->name('clan.invite');
Route::get('/clan/request/{joinRequest}', [ClanController::class, 'cancelRequest'])->name('clan.request.cancel');
Route::get('/clan/information', [ClanController::class, 'information'])->name('clan.information');
Route::get('/clan/logs', [ClanController::class, 'logs'])->name('clan.logs');
Route::get('/clan/quests', [ClanController::class, 'quests'])->name('clan.quests');
Route::post('/clan/information/description', [ClanController::class, 'saveDescription'])->name('clan.information.save-description');
Route::post('/clan/information/news', [ClanController::class, 'saveNews'])->name('clan.information.save-news');
Route::get('/clan', [ClanController::class, 'index'])->name('clan');
Route::post('/clan', [ClanController::class, 'store'])->name('clan.store');
Route::get('/clan/skills', [ClanSkillController::class, 'index'])->name('clan.skills');
Route::post('/clan/skills/{id}/learn', [ClanSkillController::class, 'learn'])->name('clan.skills.learn');

Route::match(['get', 'post'], '/clan-warehouse/{id}', [ClanWarehouseController::class, 'put'])->name('clan.warehouse');
Route::match(['get', 'post'], '/clan-warehouse/{id}/take', [ClanWarehouseController::class, 'take'])->name('clan.warehouse.take');
Route::get('/clan-warehouse/{id}/logs', [ClanWarehouseController::class, 'logs'])->name('clan.warehouse.logs');
Route::match(['get', 'post'], '/clan-warehouse/{id}/treasury', [ClanTreasuryController::class, 'index'])->name('clan.treasury');

Route::get('/who/clan', [ClanController::class, 'membersFrame'])->name('who.clan');