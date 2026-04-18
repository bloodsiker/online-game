<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Infrastructure\Persistence;

use App\Modules\Structure\Exchange\Domain\Contracts\ExchangeReadRepository;
use App\Modules\Structure\Exchange\Infrastructure\Persistence\Models\Exchange;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Support\Collection;

class EloquentExchangeReadRepository implements ExchangeReadRepository
{
    public function findStructureOrFail(int $id): Structure
    {
        return Structure::with('npc')->findOrFail($id);
    }

    public function getExchangeItems(int $structureId): Collection
    {
        return Exchange::with(['fromItem', 'toItem'])
            ->where('structure_id', $structureId)
            ->orderBy('sort_order')
            ->get();
    }

    public function findExchangeItem(int $structureId, int $fromShareItemId, int $toShareItemId): ?Exchange
    {
        return Exchange::with(['fromItem', 'toItem'])
            ->where('structure_id', $structureId)
            ->where('from_share_item_id', $fromShareItemId)
            ->where('to_share_item_id', $toShareItemId)
            ->first();
    }
}
