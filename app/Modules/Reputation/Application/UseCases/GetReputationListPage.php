<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Application\DTOs\ReputationListPageDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;

class GetReputationListPage
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
    ) {}

    public function execute(Player $player): ReputationListPageDTO
    {
        $reputations = $this->readRepository->getAllReputations();

        $playerReputations = $reputations->map(function ($reputation) use ($player) {
            $pr = $this->reputationService->getOrCreate($player, $reputation);
            $currentTier = $this->reputationService->getCurrentTier($reputation, $pr->points);
            $nextTier = $reputation->tiers->where('min_points', '>', $pr->points)->sortBy('min_points')->first();

            return [
                'reputation' => $reputation,
                'pr' => $pr,
                'currentTier' => $currentTier,
                'nextTier' => $nextTier,
            ];
        });

        return new ReputationListPageDTO($playerReputations, 'reputation');
    }
}
