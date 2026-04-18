<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\UseCases;

use App\Modules\Structure\Exchange\Application\DTOs\ExchangePageDTO;
use App\Modules\Structure\Exchange\Application\Mappers\ExchangePageViewMapper;
use App\Modules\Structure\Exchange\Domain\Contracts\ExchangeInventoryRepository;
use App\Modules\Structure\Exchange\Domain\Contracts\ExchangeReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;

class GetExchangePage
{
    public function __construct(
        private readonly ExchangeReadRepository $readRepository,
        private readonly ExchangeInventoryRepository $inventoryRepository,
        private readonly ExchangePageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $exchangeId): ExchangePageDTO
    {
        $exchange = $this->readRepository->findStructureOrFail($exchangeId);

        if ($user->location_id !== $exchange->npc->location_id) {
            throw new DomainException('Вы находитесь не в том месте для обмена.');
        }

        $availableCounts = $this->inventoryRepository
            ->getBackpackItems($user)
            ->mapWithKeys(static fn ($item) => [(int) $item->item->share_item_id => (int) $item->count])
            ->all();

        return $this->mapper->map(
            $user,
            $exchange->id,
            $this->readRepository->getExchangeItems($exchange->id),
            $availableCounts,
        );
    }
}
