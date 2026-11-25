<?php

declare(strict_types=1);

use App\Modules\SecretKnowledge\Presentation\Http\SecretKnowledgeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/npc/{npc}/secret-knowledge', [SecretKnowledgeController::class, 'index'])->name('secret_knowledge.index');
    Route::post('/npc/{npc}/secret-knowledge/bundle/start', [SecretKnowledgeController::class, 'startStudy'])
        ->middleware('throttle:30,1')->name('secret_knowledge.bundle.start');
    Route::post('/npc/{npc}/secret-knowledge/bundle/complete', [SecretKnowledgeController::class, 'completeStudy'])
        ->middleware('throttle:30,1')->name('secret_knowledge.bundle.complete');
    Route::post('/npc/{npc}/secret-knowledge/bundle/cancel', [SecretKnowledgeController::class, 'cancelStudy'])
        ->name('secret_knowledge.bundle.cancel');
    Route::post('/npc/{npc}/secret-knowledge/study/{manual}', [SecretKnowledgeController::class, 'study'])->name('secret_knowledge.study');
});
