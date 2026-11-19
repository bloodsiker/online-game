<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\Services;

use App\Modules\Reputation\Domain\Services\CityGuardPerksService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Скидка репутации «Городская стража» у торговцев Нейрина: обычные магазины
 * (type=shop, без своей репутации), стоящие на карте города — сами или их НПС.
 * Скидывается только цена в монетах; бриллианты и бартер не меняются.
 */
class CityShopDiscountService
{
    public const CITY_MAP_ID = 1;

    public function __construct(
        private readonly CityGuardPerksService $guardPerks = new CityGuardPerksService,
    ) {}

    public function percentFor(User $user, int $structureId): int
    {
        if ($user->player_id === null || ! $this->isCityShop($structureId)) {
            return 0;
        }

        return $this->guardPerks->shopDiscountPercent((int) $user->player_id);
    }

    public static function apply(int $price, int $percent): int
    {
        if ($price <= 0 || $percent <= 0) {
            return $price;
        }

        return max(1, (int) floor($price * (100 - $percent) / 100));
    }

    private function isCityShop(int $structureId): bool
    {
        $shop = DB::table('structures as s')
            ->leftJoin('npcs as n', 'n.id', '=', 's.npc_id')
            ->leftJoin('locations as l', 'l.id', '=', DB::raw('COALESCE(n.location_id, s.location_id)'))
            ->where('s.id', $structureId)
            ->first(['s.type', 's.reputation_id', 'l.map_id']);

        return $shop !== null
            && $shop->type === Structure::TYPE_SHOP
            && $shop->reputation_id === null
            && (int) $shop->map_id === self::CITY_MAP_ID;
    }
}
