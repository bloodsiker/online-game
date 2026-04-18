<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class DeclineReputationQuest
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
    ) {}

    public function execute(User $user, int $id): int
    {
        $player = $user->player;
        $reputation = $this->readRepository->findReputationForIndexOrFail($id);
        $pr = $this->reputationService->getOrCreate($player, $reputation);
        $pr->update(['last_completed_at' => now()]);

        session()->forget('rep_offer_'.$player->id.'_'.$id);

        return (int) $reputation->npc_id;
    }
}
