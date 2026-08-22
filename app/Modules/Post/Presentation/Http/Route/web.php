<?php

declare(strict_types=1);

use App\Modules\Post\Presentation\Http\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/post', [PostController::class, 'index'])->name('post');
Route::get('/post/unread-count', [PostController::class, 'unreadCount'])
    ->withoutMiddleware('updateLastOnline')
    ->name('post.unread-count');
Route::get('/post/letter/{id}', [PostController::class, 'letter'])->name('post.letter');
Route::post('/post/send', [PostController::class, 'send'])->name('post.send');
Route::post('/post/bulk', [PostController::class, 'bulk'])->name('post.bulk');
Route::get('/post/letter/{id}/delete', [PostController::class, 'delete'])->name('post.delete');
Route::get('/post/letter/{id}/claim', [PostController::class, 'claim'])->name('post.claim');
