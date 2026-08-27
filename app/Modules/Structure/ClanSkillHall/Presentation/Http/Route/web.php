<?php

use App\Modules\Structure\ClanSkillHall\Presentation\Http\ClanSkillHallController;
use Illuminate\Support\Facades\Route;

Route::get('/clan-skill-hall/{id}', [ClanSkillHallController::class, 'index'])->name('clan_skill_hall');
Route::post('/clan-skill-hall/{id}/learn/{skillId}', [ClanSkillHallController::class, 'learn'])->name('clan_skill_hall.learn');
