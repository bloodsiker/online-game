<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\ReputationExchange\Application\DTOs\ReputationExchangePageDTO;
use App\Modules\Structure\ReputationExchange\Application\DTOs\ReputationExchangeViewItemDTO;
use App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models\ReputationExchange;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;

class GetReputationExchangePage
{
    public function __construct(
        private readonly ReputationService $reputationService,
    ) {}

    public function execute(User $user, int $structureId): ReputationExchangePageDTO
    {
        $structure = Structure::with('npc')->findOrFail($structureId);

        if ($user->location_id !== $structure->npc->location_id) {
            throw new DomainException('Вы находитесь не в том месте для обмена.');
        }

        $reputation = Reputation::where('npc_id', $structure->npc_id)->firstOrFail();
        $currentPoints = $this->reputationService->getOrCreate($user->player, $reputation)->points;

        $exchangeItems = ReputationExchange::with('shareItem')
            ->where('structure_id', $structureId)
            ->orderBy('sort_order')
            ->get();

        $availableCounts = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->get()
            ->mapWithKeys(static fn ($item) => [(int) $item->item->share_item_id => (int) $item->count])
            ->all();

        $items = $exchangeItems->map(
            static fn (ReputationExchange $item): ReputationExchangeViewItemDTO => new ReputationExchangeViewItemDTO(
                shareItemId: (int) $item->shareItem->id,
                name: (string) $item->shareItem->name,
                image: (string) $item->shareItem->image,
                rarityColor: (string) $item->shareItem->rarity?->color(),
                points: (int) $item->points,
                minReputation: (int) $item->min_reputation,
                maxReputation: (int) $item->max_reputation,
                availableCount: $availableCounts[(int) $item->share_item_id] ?? 0,
                isCurrentBracket: $item->isAcceptedAt($currentPoints),
            )
        )->all();

        return new ReputationExchangePageDTO(
            structureId: $structureId,
            reputationName: (string) $reputation->name,
            currentPoints: $currentPoints,
            items: $items,
        );
    }
}
