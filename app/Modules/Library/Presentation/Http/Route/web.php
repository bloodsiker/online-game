<?php

declare(strict_types=1);

use App\Modules\Library\Presentation\Http\LegacyLibraryController;
use App\Modules\Library\Presentation\Http\LibraryController;
use App\Modules\Library\Presentation\Http\LibrarySitemapController;
use App\Modules\Library\Presentation\Http\Middleware\ApplyLibraryLocale;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [LibrarySitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [LibrarySitemapController::class, 'robots'])->name('robots');

Route::middleware('web')->group(function (): void {
    Route::get('/library', LegacyLibraryController::class)->name('library.index');
    Route::get('/library/{slug}', LegacyLibraryController::class)->name('library.show');

    Route::prefix('{locale}/library')->where(['locale' => 'ru|uk'])
        ->middleware(ApplyLibraryLocale::class)->name('library.localized.')
        ->group(function (): void {
            Route::get('/', [LibraryController::class, 'index'])->name('index');
            Route::get('/{slug}', [LibraryController::class, 'show'])->name('show');
        });
});
