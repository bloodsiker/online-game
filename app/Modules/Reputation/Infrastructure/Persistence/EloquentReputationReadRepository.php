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
        return Reputation::with([
            'shopItems.item',
            'shopItems.requirements.item',
            'categories' => fn ($q) => $q->where('is_active', true)->orderBy('id'),
            'npc',
        ])->findOrFail($id);
    }

    /** Товары магазина репутации в указанной категории (или без категории, если $categoryId === null). */
    public function getShopItemsByCategory(int $reputationId, ?int $categoryId): Collection
    {
        return ReputationShopItem::where('reputation_id', $reputationId)
            ->when($categoryId !== null, fn ($q) => $q->where('share_structure_category_id', $categoryId))
            ->when($categoryId === null, fn ($q) => $q->whereNull('share_structure_category_id'))
            ->with('item', 'requirements.item')
            ->orderBy('sort_order')
            ->get();
    }

    public function findShopItemOrFail(int $reputationId, int $itemId): ReputationShopItem
    {
        return ReputationShopItem::where('reputation_id', $reputationId)
            ->with('item', 'requirements.item')
            ->findOrFail($itemId);
    }
}
