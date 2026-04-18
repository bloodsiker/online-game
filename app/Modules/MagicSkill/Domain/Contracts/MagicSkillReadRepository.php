<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Domain\Contracts;

use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Collection;

interface MagicSkillReadRepository
{
    public function getPlayerPageData(Player $player): array;

    public function findOwnedSkill(Player $player, int $skillId): ?MagicSkill;

    public function findAllyTarget(Player $player, ?int $targetPlayerId): ?Player;
}
