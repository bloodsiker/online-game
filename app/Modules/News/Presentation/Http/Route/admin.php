<?php

declare(strict_types=1);

use App\Modules\News\Presentation\Http\Admin\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/news', [NewsController::class, 'list'])->name('news');
Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
Route::post('/news/create', [NewsController::class, 'store'])->name('news.store');
Route::post('/news/upload-image', [NewsController::class, 'uploadImage'])->name('news.upload-image');
Route::match(['GET', 'POST'], '/news/{news}', [NewsController::class, 'info'])->name('news.info');
Route::get('/news/{news}/delete', [NewsController::class, 'delete'])->name('news.delete');
Route::get('/news/{news}/comment/{comment}/toggle', [NewsController::class, 'toggleComment'])->name('news.comment.toggle');
Route::get('/news/{news}/comment/{comment}/delete', [NewsController::class, 'deleteComment'])->name('news.comment.delete');
