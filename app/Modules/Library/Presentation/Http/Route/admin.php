<?php

declare(strict_types=1);

use App\Modules\Library\Presentation\Http\Admin\LibraryArticleController;
use App\Modules\Library\Presentation\Http\Admin\LibraryCategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/library/categories', [LibraryCategoryController::class, 'index'])->name('library.categories.index');
Route::get('/library/categories/create', [LibraryCategoryController::class, 'create'])->name('library.categories.create');
Route::post('/library/categories', [LibraryCategoryController::class, 'store'])->name('library.categories.store');
Route::get('/library/categories/{category}/edit', [LibraryCategoryController::class, 'edit'])->name('library.categories.edit');
Route::put('/library/categories/{category}', [LibraryCategoryController::class, 'update'])->name('library.categories.update');
Route::delete('/library/categories/{category}', [LibraryCategoryController::class, 'destroy'])->name('library.categories.destroy');
Route::get('/library/articles', [LibraryArticleController::class, 'index'])->name('library.articles.index');
Route::get('/library/articles/create', [LibraryArticleController::class, 'create'])->name('library.articles.create');
Route::post('/library/articles', [LibraryArticleController::class, 'store'])->name('library.articles.store');
Route::post('/library/articles/upload-image', [LibraryArticleController::class, 'uploadImage'])->name('library.articles.upload-image');
Route::get('/library/articles/{article}/edit', [LibraryArticleController::class, 'edit'])->name('library.articles.edit');
Route::put('/library/articles/{article}', [LibraryArticleController::class, 'update'])->name('library.articles.update');
Route::delete('/library/articles/{article}', [LibraryArticleController::class, 'destroy'])->name('library.articles.destroy');
