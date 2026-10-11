<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;

class ExperienceService
{
    public const DEATH_PENALTY_EXPERIENCE = 0.1;

    public function __construct(private PlayerStatService $statService) {}

    /**
     * Рассчитывает фактически начисляемый опыт с учётом персонального
     * коэффициента игрока и временного баффа «Прилив опыта» (эликсиры из
     * премиум-магазина, см. ActiveEffectType::EXP_BOOST) — бафф умножает
     * результат ДОПОЛНИТЕЛЬНО к статическому коэффициенту, не заменяет его.
     * Значение не сохраняется — способ сохранения остаётся у вызывающего
     * сценария.
     */
    public function calculateGain(Player $player, int $baseExperience): int
    {
        $multiplier = max(0.0, (float) $player->experience_multiplier);
        $multiplier *= 1 + $this->activeExpBoostPercent($player) / 100;

        return (int) round(max(0, $baseExperience) * $multiplier);
    }

    /** Активный процент «Прилив опыта» (0, если баффа нет или он истёк). */
    private function activeExpBoostPercent(Player $player): float
    {
        $boost = PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->where('type', ActiveEffectType::EXP_BOOST)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('current_value')
            ->first();

        return max(0.0, (float) ($boost?->current_value ?? 0));
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
