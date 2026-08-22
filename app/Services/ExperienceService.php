<?php

declare(strict_types=1);

namespace App\Services;

use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class ExperienceService
{
    public const DEATH_PENALTY_EXPERIENCE = 0.1;

    public function __construct(private PlayerStatService $statService) {}

    /**
     * Рассчитывает фактически начисляемый опыт с учётом персонального
     * коэффициента игрока. Значение не сохраняется — способ сохранения
     * остаётся у вызывающего сценария.
     */
    public function calculateGain(Player $player, int $baseExperience): int
    {
        $multiplier = max(0.0, (float) $player->experience_multiplier);

        return (int) round(max(0, $baseExperience) * $multiplier);
    }

    public function lostExpAfterDeath(Player $player): void
    {
        $lostExp = round($player->exp_diff * self::DEATH_PENALTY_EXPERIENCE);
        $sheet = $this->statService->resolve($player);

        $player->user->location_id = $player->user->currentLocation->map->resp_location_id;

        $player->death++;
        $player->hp_now = $sheet->getHpMax();
        $player->mp_now = $sheet->getMpMax();
        $player->exp = max($player->exp_up - $player->exp_diff, $player->exp - $lostExp);
        $player->push();
    }
}
