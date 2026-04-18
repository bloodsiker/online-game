<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Domain\Contracts;

use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Carbon\CarbonInterface;

interface MagicSkillWriteRepository
{
    public function syncEquippedSkills(Player $player, array $equippedIds): void;

    public function updateSortOrder(Player $player, array $skillIds): void;

    public function consumeMana(Player $player, int $manaCost): void;

    public function savePlayers(Player $caster, Player $target): void;

    public function updateCooldown(Player $player, MagicSkill $skill, ?CarbonInterface $cooldownEndsAt): void;
}
