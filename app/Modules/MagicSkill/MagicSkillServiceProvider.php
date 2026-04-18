<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill;

use App\Modules\MagicSkill\Domain\Contracts\MagicSkillReadRepository;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillWriteRepository;
use App\Modules\MagicSkill\Infrastructure\Persistence\EloquentMagicSkillRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MagicSkillServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MagicSkillReadRepository::class, EloquentMagicSkillRepository::class);
        $this->app->bind(MagicSkillWriteRepository::class, EloquentMagicSkillRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'magic-skill');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
