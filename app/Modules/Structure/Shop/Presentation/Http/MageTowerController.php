<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Presentation\Http;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

/** Башня магов использует общие механики магазина для покупки гримуаров и продажи вещей. */
class MageTowerController extends ShopController
{
    protected function expectedType(): string
    {
        return Structure::TYPE_MAGE_TOWER;
    }
}
