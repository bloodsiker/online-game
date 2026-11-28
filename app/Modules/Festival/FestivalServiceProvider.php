<?php

declare(strict_types=1);

namespace App\Modules\Festival;

use App\Modules\Festival\Application\Services\FestivalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class FestivalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'festival');
        Route::middleware('web')->group(__DIR__.'/Presentation/Http/Route/web.php');
        // Пункт «Фестиваль» на странице НПС, если это Смотритель и игрок на его локации.
        View::composer('npc::index', function ($view): void {
            $settings = Auth::check() ? app(FestivalService::class)->settings() : null;
            $npcId = $settings !== null && (int) $settings->npc?->location_id === (int) Auth::user()->location_id
                ? (int) $settings->npc_id
                : null;
            $view->with('festivalNpcId', $npcId);
        });
    }
}
