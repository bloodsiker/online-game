<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Domain\Contracts;

use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuctionListingsReader
{
    public function page(int $page, int $perPage, ?int $ownerId = null, string $name = '', ?ShareItemType $type = null, ?ItemRarity $rarity = null): LengthAwarePaginator;
}
