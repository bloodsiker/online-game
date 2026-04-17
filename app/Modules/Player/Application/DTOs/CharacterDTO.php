<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\DTOs;

use App\Modules\Player\Domain\DTO\StatSheet;

final readonly class CharacterDTO
{
    /**
     * @param  PlayerSkillDTO[]  $skills
     */
    public function __construct(
        // Identity
        public string $playerName,
        public int $level,
        public string $raceName,
        // Resources
        public int $hpNow,
        public int $mpNow,
        // Economy
        public int $money,
        // Progress
        public int $exp,
        public int $expUp,
        public float $expPercent,
        // Battle record
        public int $victory,
        public int $death,
        // Stat allocation
        public int $freeStats,
        // Base stats (without equipment bonuses)
        public int $baseStrength,
        public int $baseIntuition,
        public int $baseAgility,
        public int $baseIntelligence,
        public int $baseWisdom,
        // Full resolved stat sheet (with equipment, passives, buffs)
        public StatSheet $stats,
        // Skills
        public array $skills,
    ) {}
}
