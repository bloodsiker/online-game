<?php

declare(strict_types=1);

use App\Modules\News\Presentation\Http\LaunchSubscriptionController;
use App\Modules\News\Presentation\Http\NewsController;
use Illuminate\Support\Facades\Route;

Route::post('/news/{news}/comment', [NewsController::class, 'comment'])->name('news.comment');
Route::get('/', [NewsController::class, 'index'])->name('index');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::post('/homepage/language', [NewsController::class, 'language'])->name('homepage.language');
Route::post('/homepage/launch-subscription', LaunchSubscriptionController::class)
    ->middleware('throttle:6,1')->name('homepage.launch-subscription');
