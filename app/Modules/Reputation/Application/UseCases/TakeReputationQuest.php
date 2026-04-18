<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\UseCases;

use App\Modules\Reputation\Application\DTOs\ReputationActionResultDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class TakeReputationQuest
{
    public function __construct(
        private readonly ReputationReadRepository $readRepository,
        private readonly ReputationService $reputationService,
    ) {}

    public function execute(User $user, int $id): ReputationActionResultDTO
    {
        $reputation = $this->readRepository->findReputationForIndexOrFail($id);
        $player = $user->player;

        if (! $this->reputationService->canTakeQuest($player, $reputation)) {
            return new ReputationActionResultDTO(false, 'Вы не можете взять новый квест прямо сейчас.');
        }

        $questPlayer = $this->reputationService->assignQuest($player, $reputation);

        if (! $questPlayer) {
            return new ReputationActionResultDTO(false, 'Нет доступных заданий для вашего уровня репутации.');
        }

        session()->forget('rep_offer_'.$player->id.'_'.$id);

        return new ReputationActionResultDTO(true, 'Задание получено!', 'success');
    }
}
