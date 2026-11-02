<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Models\BackpackEntry;
use App\Modules\TreasureHunt\Application\Services\AssignTreasureDestination;
use App\Modules\TreasureHunt\Application\Services\TreasureHuntService;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureHunt;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class TreasureHuntServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach ([Backpack::class, BackpackEntry::class] as $entryClass) {
            $entryClass::created(static function ($entry): void {
                app(AssignTreasureDestination::class)
                    ->execute((int) $entry->user_id, (int) $entry->item_id, knownItem: $entry->relationLoaded('item') ? $entry->getRelation('item') : null);
            });
        }
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'treasure');
        Route::middleware('web')->group(__DIR__.'/Presentation/Http/Route/web.php');
        View::composer('location::index', function ($view): void {
            $view->with('treasureHunt', Auth::check() ? app(TreasureHuntService::class)->activeAtLocation(Auth::user()) : null);
        });
        User::updated(static function (User $user): void {
            if ($user->wasChanged('location_id')) {
                TreasureHunt::cancelSearchForUser((int) $user->id);
            }
        });
    }
}
