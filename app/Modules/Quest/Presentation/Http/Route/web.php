<?php

use App\Modules\Quest\Presentation\Http\QuestController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::post('/quest/{id}/take', [QuestController::class, 'take'])->name('quest.take');
    Route::post('/quest/{id}/complete', [QuestController::class, 'complete'])->name('quest.complete');
    Route::post('/quest/{id}/cancel', [QuestController::class, 'cancelQuest'])->name('quest.cancel');
    Route::post('/quest/clan/{id}/cancel', [QuestController::class, 'cancelClanQuest'])->name('quest.clan.cancel');
    Route::get('/quest/{id}', [QuestController::class, 'quest'])->name('quest');
    Route::get('/quests', [QuestController::class, 'list'])->name('quests');
});
