<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Application\DTOs\ReputationListPageDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputationMedal;

class GetReputationListPage
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
    ) {}

    public function execute(Player $player): ReputationListPageDTO
    {
        $reputations = $this->readRepository->getAllReputations();
        $earnedTierIds = PlayerReputationMedal::query()
            ->where('player_id', $player->id)
            ->where('is_feat', false)
            ->pluck('tier_id')
            ->all();

        $playerReputations = $reputations->map(function ($reputation) use ($player, $earnedTierIds) {
            $pr = $this->reputationService->getOrCreate($player, $reputation);
            $currentTier = $this->reputationService->getCurrentTier($reputation, $pr->points);
            $nextTier = $reputation->tiers->where('min_points', '>', $pr->points)->sortBy('min_points')->first();
            $earnedMedalTier = $reputation->tiers
                ->whereIn('id', $earnedTierIds)
                ->filter(fn ($tier) => $tier->medal_name && $tier->medal_icon)
                ->sortByDesc('min_points')
                ->first();

            return [
                'reputation' => $reputation,
                'pr' => $pr,
                'currentTier' => $currentTier,
                'nextTier' => $nextTier,
                'earnedMedalTier' => $earnedMedalTier,
            ];
        });

        return new ReputationListPageDTO($playerReputations, (int) $player->reputation_rating, 'reputation');
    }
}
