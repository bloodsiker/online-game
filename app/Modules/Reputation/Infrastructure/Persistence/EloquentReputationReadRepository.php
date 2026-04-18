<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Infrastructure\Persistence;

use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopItem;
use Illuminate\Support\Collection;

class EloquentReputationReadRepository implements ReputationReadRepository
{
    public function getAllReputations(): Collection
    {
        return Reputation::with('tiers', 'npc')->get();
    }

    public function findReputationForIndexOrFail(int $id): Reputation
    {
        return Reputation::with('tiers.quests.quest', 'npc')->findOrFail($id);
    }

    public function findReputationForShopOrFail(int $id): Reputation
    {
        return Reputation::with('shopItems.item', 'shopItems.requirements.item', 'npc')->findOrFail($id);
    }

    public function findShopItemOrFail(int $reputationId, int $itemId): ReputationShopItem
    {
        return ReputationShopItem::where('reputation_id', $reputationId)
            ->with('item', 'requirements.item')
            ->findOrFail($itemId);
    }
}
