<?php

declare(strict_types=1);

use App\Modules\Structure\Workshop\Presentation\Http\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::post('/profession/recipe/learn/{item}', [WorkshopController::class, 'learn'])->name('profession.recipe.learn');
Route::post('/workshop/{id}/craft/{recipe}', [WorkshopController::class, 'craft'])->name('workshop.craft');
Route::get('/workshop/{id}', [WorkshopController::class, 'index'])->name('workshop');
