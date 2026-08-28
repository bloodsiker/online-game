<?php

declare(strict_types=1);

use App\Modules\MagicSkill\Presentation\Http\MagicSkillController;
use Illuminate\Support\Facades\Route;

Route::group([], function (): void {
    Route::post('/magic-skill/update', [MagicSkillController::class, 'updateSkill'])->name('magic_skill.update');
    Route::post('/magic-skill/order', [MagicSkillController::class, 'updateOrder'])->name('magic_skill.order');
    Route::post('/magic-skill/{skill}/use', [MagicSkillController::class, 'useSkill'])->name('magic_skill.use');
    Route::post('/magic-skill/learn/{item}', [MagicSkillController::class, 'learnFromBook'])->name('magic_skill.learn');
    Route::get('/magic-skill/info/{skill}', [MagicSkillController::class, 'info'])->name('magic_skill.info');
    Route::get('/magic-skill', [MagicSkillController::class, 'index'])->name('magic_skill');
});
