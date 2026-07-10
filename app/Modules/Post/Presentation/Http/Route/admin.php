<?php

declare(strict_types=1);

use App\Modules\Post\Presentation\Http\Admin\PostAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/post/send', [PostAdminController::class, 'index'])->name('post.send');
Route::post('/post/send', [PostAdminController::class, 'send'])->name('post.send.store');
