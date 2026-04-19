<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases;

use App\Modules\Player\Domain\Factories\PlayerFactory;
use App\Modules\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class RegisterPlayerProfile
{
    public function __construct(
        private readonly PlayerRepositoryInterface $playerRepository,
        private readonly PlayerFactory $playerFactory,
    ) {}

    public function execute(User $user, int $raceId): Player
    {
        $experience = $this->playerRepository->getInitialExperienceForLevel(1);
        $player = $this->playerFactory->create($user, $raceId, $experience);

        return $this->playerRepository->register($user, $player);
    }
}
