<?php

declare(strict_types=1);

namespace App\Modules\Quest;

use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestDialogue;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestStage;
use App\Modules\Quest\Infrastructure\Persistence\Observers\QuestDefinitionObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class QuestServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $questDefinitionObserver = QuestDefinitionObserver::class;
        Quest::observe($questDefinitionObserver);
        QuestStage::observe($questDefinitionObserver);
        QuestObjective::observe($questDefinitionObserver);
        QuestReward::observe($questDefinitionObserver);
        QuestDialogue::observe($questDefinitionObserver);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'quest');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
