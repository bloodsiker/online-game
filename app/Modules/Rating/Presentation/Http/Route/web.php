<?php

declare(strict_types=1);

use App\Modules\Rating\Presentation\Http\RatingController;
use Illuminate\Support\Facades\Route;

Route::get('/rating', [RatingController::class, 'index'])->name('rating');
Route::get('/rating/search', [RatingController::class, 'search'])->name('rating.search');
