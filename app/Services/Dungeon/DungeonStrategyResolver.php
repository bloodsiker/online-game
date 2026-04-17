<?php

declare(strict_types=1);

namespace App\Services\Dungeon;

use App\Enums\DungeonType;
use App\Models\Dungeon\Dungeon;
use InvalidArgumentException;

class DungeonStrategyResolver
{
    public function __construct(
        private readonly LinearDungeonStrategy $linear,
        private readonly SurvivalDungeonStrategy $survival,
        private readonly BossRushDungeonStrategy $bossRush,
    ) {}

    public function resolve(Dungeon $dungeon): DungeonStrategyInterface
    {
        return match ($dungeon->type) {
            DungeonType::LINEAR => $this->linear,
            DungeonType::SURVIVAL => $this->survival,
            DungeonType::BOSS_RUSH => $this->bossRush,
            default => throw new InvalidArgumentException("Unknown dungeon type: {$dungeon->type->value}"),
        };
    }
}
