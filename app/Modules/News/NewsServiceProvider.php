<?php

declare(strict_types=1);

namespace App\Modules\News;

use App\Modules\News\Domain\Contracts\NewsReadRepository;
use App\Modules\News\Infrastructure\Persistence\EloquentNewsReadRepository;
use App\Modules\News\Presentation\Console\AnnounceGameLaunch;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NewsReadRepository::class, EloquentNewsReadRepository::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([AnnounceGameLaunch::class]);
        }

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'news');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');

        Route::middleware(['web', 'isAdmin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__.'/Presentation/Http/Route/admin.php');
    }
}
