<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\UseCases;

use App\Models\Structure;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Structure\Exchange\Application\DTOs\ExchangeItemDTO;
use App\Modules\Structure\Exchange\Application\DTOs\ExchangePageDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;
use Illuminate\Support\Collection;

class GetExchangePage
{
    public function __construct(
        private readonly BackpackService $backpackService,
    ) {}

    public function execute(User $user, int $exchangeId): ExchangePageDTO
    {
        $exchange = Structure::with(['npc', 'exchangeItems.fromItem', 'exchangeItems.toItem'])->findOrFail($exchangeId);

        if ($user->location_id !== $exchange->npc->location_id) {
            throw new DomainException('Вы находитесь не в том месте для обмена.');
        }

        $bagItems = $this->getUserBackpackItems($user);

        return new ExchangePageDTO(
            exchange: $exchange,
            items: $exchange->exchangeItems
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($item) => $this->mapItem($item, $bagItems)),
        );
    }

    private function mapItem(mixed $exchangeItem, Collection $bagItems): ExchangeItemDTO
    {
        return new ExchangeItemDTO(
            id: $exchangeItem->id,
            fromItem: $exchangeItem->fromItem,
            toItem: $exchangeItem->toItem,
            fromAmount: $exchangeItem->from_amount,
            toAmount: $exchangeItem->to_amount,
            availableCount: $bagItems->get((string) $exchangeItem->fromItem->id, 0),
        );
    }

    private function getUserBackpackItems(User $user): Collection
    {
        return $this->backpackService
            ->getBaseQuery($user)
            ->addSelect('items.share_item_id')
            ->get()
            ->pluck('count', 'share_item_id');
    }
}
