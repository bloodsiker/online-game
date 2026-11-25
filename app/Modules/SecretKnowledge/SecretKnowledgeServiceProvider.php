<?php

declare(strict_types=1);

namespace App\Modules\SecretKnowledge;

use App\Modules\SecretKnowledge\Application\Services\SecretKnowledgeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class SecretKnowledgeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'secret-knowledge');
        Route::middleware('web')->group(__DIR__.'/Presentation/Http/Route/web.php');
        View::composer('npc::index', function ($view): void {
            $settings = app(SecretKnowledgeService::class)->settings();
            $npcId = Auth::check() && $settings !== null
                && (int) $settings->npc?->location_id === (int) Auth::user()->location_id
                ? (int) $settings->npc_id
                : null;
            $view->with('secretKnowledgeNpcId', $npcId);
        });
    }
}
