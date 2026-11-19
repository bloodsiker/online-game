<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Reputation\Application\DTOs\ReputationPageDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Domain\Services\CityGuardPerksService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputationMedal;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetReputationPage
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
        private readonly CityGuardPerksService $guardPerks = new CityGuardPerksService,
    ) {}

    public function execute(User $user, int $id): ReputationPageDTO
    {
        $reputation = $this->readRepository->findReputationForIndexOrFail($id);
        $player = $user->player;

        $pr = $this->reputationService->getOrZero($player, $reputation);
        $currentTier = $this->reputationService->getCurrentTier($reputation, $pr->points);
        $canTake = $this->reputationService->canTakeQuest($player, $reputation);
        $activeQuest = $this->reputationService->getActiveQuest($player, $reputation);
        $cooldownDiff = $this->reputationService->getCooldownDiff($player, $reputation);
        $earnedMedals = $this->reputationService->getEarnedMedals($reputation, $pr->points, $player);
        $earnedFeatMedals = $this->reputationService->getEarnedFeatMedals($reputation, $pr->points, $player);
        $earnedMedalDates = PlayerReputationMedal::query()
            ->where('player_id', $player->id)
            ->where('reputation_id', $reputation->id)
            ->get(['tier_id', 'is_feat', 'earned_at'])
            ->mapWithKeys(fn (PlayerReputationMedal $medal): array => [
                $medal->tier_id.'_'.($medal->is_feat ? 'feat' : 'regular') => $medal->earned_at?->format('d.m.Y H:i'),
            ])
            ->all();

        $progressMap = [];
        if ($activeQuest) {
            foreach ($activeQuest->objectives as $po) {
                $progressMap[$po->quest_objective_id] = $po->amount;
            }
        }

        return new ReputationPageDTO(
            reputation: $reputation,
            pr: $pr,
            currentTier: $currentTier,
            canTake: $canTake,
            activeQuest: $activeQuest,
            cooldownDiff: $cooldownDiff,
            earnedMedals: $earnedMedals,
            earnedFeatMedals: $earnedFeatMedals,
            earnedMedalDates: $earnedMedalDates,
            progressMap: $progressMap,
            message: session('rep_error') ?? session('rep_success'),
            messageType: session()->has('rep_success') ? 'success' : 'error',
            player: $player,
            group: 'reputation',
            rankPerks: $this->rankPerks($reputation, (int) $pr->points, $currentTier?->id),
        );
    }

    /** @return list<array{name: string, minPoints: int, discount: int, blessing: int, current: bool}> */
    private function rankPerks(Reputation $reputation, int $points, ?int $currentTierId): array
    {
        if ((int) $reputation->id !== CityGuardPerksService::REPUTATION_ID) {
            return [];
        }

        return $reputation->tiers
            ->filter(fn ($tier): bool => (int) $tier->min_points > 0)
            ->map(function ($tier) use ($currentTierId): array {
                [$discount, $blessing] = $this->guardPerks->perksForPoints((int) $tier->min_points);

                return [
                    'name' => (string) ($tier->medal_name ?? ''),
                    'minPoints' => (int) $tier->min_points,
                    'discount' => $discount,
                    'blessing' => $blessing,
                    'current' => (int) $tier->id === $currentTierId,
                ];
            })
            ->values()
            ->all();
    }
}
