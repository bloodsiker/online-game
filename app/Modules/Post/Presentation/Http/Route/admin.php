<?php

declare(strict_types=1);

use App\Modules\Post\Presentation\Http\Admin\AdministrationMailController;
use App\Modules\Post\Presentation\Http\Admin\PostAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/post/send', [PostAdminController::class, 'index'])->name('post.send');
Route::post('/post/send', [PostAdminController::class, 'send'])->name('post.send.store');
Route::get('/post/administration', [AdministrationMailController::class, 'index'])->name('post.administration.index');
Route::get('/post/administration/{id}', [AdministrationMailController::class, 'show'])->name('post.administration.show');
Route::post('/post/administration/{id}/reply', [AdministrationMailController::class, 'reply'])->middleware('throttle:20,1')->name('post.administration.reply');
