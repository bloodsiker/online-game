<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Reputation\Application\DTOs\ReputationActionResultDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Application\Services\ReputationShopCartService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class AddReputationCartItem
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
        private readonly ReputationShopCartService $cartService,
    ) {}

    public function execute(User $user, int $reputationId, int $shopItemId, int $quantity): ReputationActionResultDTO
    {
        $reputation = $this->readRepository->findReputationForShopOrFail($reputationId);

        if (! $this->isAtNpcLocation($user, $reputation)) {
            return new ReputationActionResultDTO(false, 'Магазин доступен только находясь рядом с НПС.');
        }

        $shopItem = $this->readRepository->findShopItemOrFail($reputation->id, $shopItemId);
        $pr = $this->reputationService->getOrCreate($user->player, $reputation);

        if ($pr->points < $shopItem->min_points) {
            return new ReputationActionResultDTO(false, 'Недостаточно очков репутации для этого товара.');
        }

        $this->cartService->addItem($user, $shopItem->id, max(1, $quantity));

        return new ReputationActionResultDTO(true, '', 'success');
    }

    private function isAtNpcLocation(User $user, Reputation $reputation): bool
    {
        if (! $reputation->npc || ! $reputation->npc->location_id) {
            return true;
        }

        return (int) $user->currentLocation?->id === (int) $reputation->npc->location_id;
    }
}
