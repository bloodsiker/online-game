<?php

declare(strict_types=1);

use App\Modules\Forum\Presentation\Http\Admin\ForumSectionController;
use Illuminate\Support\Facades\Route;

Route::get('/forum/sections', [ForumSectionController::class, 'index'])->name('forum.sections.index');
Route::get('/forum/sections/create', [ForumSectionController::class, 'create'])->name('forum.sections.create');
Route::post('/forum/sections', [ForumSectionController::class, 'store'])->name('forum.sections.store');
Route::get('/forum/sections/{section}/edit', [ForumSectionController::class, 'edit'])->name('forum.sections.edit');
Route::put('/forum/sections/{section}', [ForumSectionController::class, 'update'])->name('forum.sections.update');
Route::delete('/forum/sections/{section}', [ForumSectionController::class, 'destroy'])->name('forum.sections.destroy');
