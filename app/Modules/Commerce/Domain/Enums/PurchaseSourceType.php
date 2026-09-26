<?php

declare(strict_types=1);

namespace App\Modules\Commerce\Domain\Enums;

enum PurchaseSourceType: string
{
    case Shop = 'shop';
    case BarterShop = 'barter_shop';
    case PremiumShop = 'premium_shop';
    case ReputationShop = 'reputation_shop';
    case InfluenceShop = 'influence_shop';

    public function label(): string
    {
        return match ($this) {
            self::Shop => 'Магазин',
            self::BarterShop => 'Бартерный магазин',
            self::PremiumShop => 'Премиальный магазин',
            self::ReputationShop => 'Магазин репутации',
            self::InfluenceShop => 'Магазин влияния',
        };
    }
}
