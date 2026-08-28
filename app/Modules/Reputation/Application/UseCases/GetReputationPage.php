<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Reputation\Application\DTOs\ReputationPageDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetReputationPage
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
    ) {}

    public function execute(User $user, int $id): ReputationPageDTO
    {
        $reputation = $this->readRepository->findReputationForIndexOrFail($id);
        $player = $user->player;

        $pr = $this->reputationService->getOrCreate($player, $reputation);
        $currentTier = $this->reputationService->getCurrentTier($reputation, $pr->points);
        $canTake = $this->reputationService->canTakeQuest($player, $reputation);
        $activeQuest = $this->reputationService->getActiveQuest($player, $reputation);
        $cooldownDiff = $this->reputationService->getCooldownDiff($player, $reputation);
        $earnedMedals = $this->reputationService->getEarnedMedals($reputation, $pr->points, $player);
        $earnedFeatMedals = $this->reputationService->getEarnedFeatMedals($reputation, $pr->points, $player);

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
            progressMap: $progressMap,
            message: session('rep_error') ?? session('rep_success'),
            messageType: session()->has('rep_success') ? 'success' : 'error',
            player: $player,
            group: 'reputation',
        );
    }
}
