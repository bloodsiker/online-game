<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

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

    /**
     * Списывает штраф за смерть и возвращает фактически потерянный опыт.
     * Он меньше расчётных 10%, если игрок прошёл в уровне меньше десятой
     * части: ниже начала уровня опыт не опускается, уровень не теряется.
     */
    public function lostExpAfterDeath(Player $player): int
    {
        $lostExp = round($player->exp_diff * self::DEATH_PENALTY_EXPERIENCE);
        $sheet = $this->statService->resolve($player);
        $user = $player->user()
            ->without('player')
            ->with('currentLocation.map')
            ->firstOrFail();
        $respawnLocationId = $user->currentLocation?->map?->resp_location_id;

        if ($respawnLocationId !== null) {
            $user->prev_location_id = $user->location_id;
            $user->location_id = $respawnLocationId;
        }

        $expBefore = (int) $player->exp;

        $player->death++;
        $player->hp_now = $sheet->getHpMax();
        $player->mp_now = $sheet->getMpMax();
        $player->exp = max($player->exp_up - $player->exp_diff, $player->exp - $lostExp);
        $player->save();
        $user->save();

        // Не используем push(): у User глобально загружен player, и каскадное
        // сохранение может перезаписать новые exp/hp устаревшей копией модели.
        $player->setRelation('user', $user);

        return $expBefore - (int) $player->exp;
    }
}
