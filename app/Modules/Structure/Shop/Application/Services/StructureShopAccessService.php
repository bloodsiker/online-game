<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\Services;

use App\Modules\Festival\Application\Services\FestivalService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class StructureShopAccessService
{
    public function canVisit(User $user, Structure $shop): bool
    {
        if ($shop->reputation_id === null) {
            return true;
        }
        $locationId = $shop->npc_id ? $shop->npc?->location_id : $shop->location_id;

        return $locationId === null || (int) $locationId === (int) $user->location_id;
    }

    /** Магазин фестиваля доступен только стороне-победителю (см. модуль Festival). */
    public function festivalError(User $user, Structure $shop): ?string
    {
        return app(FestivalService::class)->shopAccessError($user, (int) $shop->id);
    }

    public function purchaseError(User $user, Structure $shop, iterable $lines): ?string
    {
        if (! $this->canVisit($user, $shop)) {
            return __('commerce.shop_requires_npc');
        }
        if ($error = $this->festivalError($user, $shop)) {
            return $error;
        }
        if ($shop->reputation_id === null) {
            return null;
        }
        $points = $this->reputationPoints($user, $shop);
        foreach ($lines as $line) {
            if ($points < (int) $line->shopItem->min_reputation_points) {
                return __('commerce.reputation_required', ['item' => $line->shopItem->item->name, 'points' => $line->shopItem->min_reputation_points]);
            }
        }

        return null;
    }

    public function reputationPoints(User $user, Structure $shop): int
    {
        if ($shop->reputation_id === null) {
            return 0;
        }

        return (int) DB::table('player_reputations')
            ->where('player_id', $user->player_id)
            ->where('reputation_id', $shop->reputation_id)->value('points');
    }
}
