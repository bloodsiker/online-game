<?php

declare(strict_types=1);

use App\Modules\News\Presentation\Http\NewsController;
use Illuminate\Support\Facades\Route;

Route::post('/news/{news}/comment', [NewsController::class, 'comment'])->name('news.comment');
Route::get('/', [NewsController::class, 'index'])->name('index');
