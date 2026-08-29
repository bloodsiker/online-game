<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Presentation\Http;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

class BarterShopController extends ShopController
{
    protected function expectedType(): string
    {
        return Structure::TYPE_BARTER_SHOP;
    }
}
