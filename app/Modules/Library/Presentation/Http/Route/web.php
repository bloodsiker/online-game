<?php

declare(strict_types=1);

use App\Modules\Library\Presentation\Http\LibraryController;
use Illuminate\Support\Facades\Route;

Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
Route::get('/library/{slug}', [LibraryController::class, 'show'])->name('library.show');
