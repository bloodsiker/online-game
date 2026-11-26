<?php

declare(strict_types=1);

namespace App\Modules\ItemSpawn;

use Illuminate\Support\ServiceProvider;

final class ItemSpawnServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'item-spawn');
    }
}
