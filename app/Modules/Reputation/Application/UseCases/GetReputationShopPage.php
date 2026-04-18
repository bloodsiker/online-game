<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Reputation\Application\DTOs\ReputationShopPageDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetReputationShopPage
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
    ) {}

    public function execute(User $user, int $id): ReputationShopPageDTO
    {
        $reputation = $this->readRepository->findReputationForShopOrFail($id);
        abort_unless($this->isAtNpcLocation($user, $reputation), 403, 'Магазин доступен только находясь рядом с НПС.');

        return new ReputationShopPageDTO(
            reputation: $reputation,
            pr: $this->reputationService->getOrCreate($user->player, $reputation),
            message: session('shop_error') ?? session('shop_success'),
            messageType: session()->has('shop_success') ? 'success' : 'error',
            player: $user->player,
        );
    }

    private function isAtNpcLocation(User $user, Reputation $reputation): bool
    {
        if (! $reputation->npc || ! $reputation->npc->location_id) {
            return true;
        }

        return (int) $user->currentLocation?->id === (int) $reputation->npc->location_id;
    }
}
