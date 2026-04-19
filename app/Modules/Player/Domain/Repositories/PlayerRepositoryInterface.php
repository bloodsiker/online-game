<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Repositories;

use App\Modules\Player\Application\DTOs\InitialExperienceDTO;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;

interface PlayerRepositoryInterface
{
    public function save(Player $player): void;

    public function register(User $user, Player $player): Player;

    public function getInitialExperienceForLevel(int $level): InitialExperienceDTO;
}
