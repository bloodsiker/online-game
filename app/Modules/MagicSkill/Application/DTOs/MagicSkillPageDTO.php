<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\DTOs;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class MagicSkillPageDTO
{
    public function __construct(
        public readonly User $user,
        public readonly Player $player,
        public readonly string $group,
        public readonly Collection $passiveSkills,
        public readonly Collection $activeSkills,
        public readonly Collection $allyTargets,
        public readonly Collection $runePassives,
    ) {}
}
