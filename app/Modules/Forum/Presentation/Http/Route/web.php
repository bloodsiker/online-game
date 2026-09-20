<?php

declare(strict_types=1);

use App\Modules\Forum\Presentation\Http\ForumController;
use Illuminate\Support\Facades\Route;

Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/search', [ForumController::class, 'search'])->name('forum.search');
Route::get('/forum/section/{slug}', [ForumController::class, 'section'])->name('forum.section');
Route::get('/forum/topic/{id}', [ForumController::class, 'topic'])->name('forum.topic');

Route::middleware(['auth'])->group(function (): void {
    Route::post('/forum/section/{slug}/topic', [ForumController::class, 'storeTopic'])->name('forum.topic.store');
    Route::post('/forum/topic/{id}/post', [ForumController::class, 'storePost'])->name('forum.post.store');
    Route::get('/forum/post/{id}/edit', [ForumController::class, 'editPost'])->name('forum.post.edit');
    Route::put('/forum/post/{id}', [ForumController::class, 'updatePost'])->name('forum.post.update');
    Route::delete('/forum/post/{id}', [ForumController::class, 'destroyPost'])->name('forum.post.destroy');
    Route::post('/forum/topic/{id}/vote', [ForumController::class, 'vote'])->name('forum.vote');
    Route::post('/forum/topic/{id}/pin', [ForumController::class, 'pin'])->name('forum.topic.pin');
    Route::post('/forum/topic/{id}/lock', [ForumController::class, 'lock'])->name('forum.topic.lock');
    Route::delete('/forum/topic/{id}', [ForumController::class, 'destroyTopic'])->name('forum.topic.destroy');
});
