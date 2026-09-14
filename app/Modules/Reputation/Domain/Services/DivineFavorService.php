<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Domain\Services;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Enums\DivineFavorType;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;

/**
 * «Милость богов»: репутации с настроенным favor_effect_type дают игроку
 * шанс на боевой эффект (лечение/яд/баф атаки), пока активен эликсир
 * божества (favor_marker_effect_id живёт на игроке как PlayerActiveEffect)
 * — либо навсегда, если игрок выполнил подвиг верхнего тира этой репутации.
 * Величина эффекта (%) берётся из текущего тира репутации, либо из
 * feat_favor_percent верхнего тира, если подвиг выполнен.
 */
class DivineFavorService
{
    public function __construct(
        private readonly ReputationService $reputationService,
    ) {}

    private const DEFAULT_FAVOR_DURATION_SECONDS = 30;

    /**
     * @return list<array{reputation: Reputation, type: DivineFavorType, chance: int, percent: int, durationSeconds: int, permanent: bool}>
     */
    public function resolve(Player $player): array
    {
        $reputations = Reputation::whereNotNull('favor_effect_type')->with('tiers')->get();

        $favors = [];
        foreach ($reputations as $reputation) {
            $favor = $this->resolveOne($player, $reputation);
            if ($favor !== null) {
                $favors[] = $favor;
            }
        }

        return $favors;
    }

    /**
     * @return array{reputation: Reputation, type: DivineFavorType, chance: int, percent: int, durationSeconds: int, permanent: bool}|null
     */
    /**
     * Милость этой репутации у игрока уже закреплена навсегда (подвиг верхнего
     * тира выполнен, либо у текущего тира уже есть обычная медаль) — эликсир
     * ей больше не нужен, см. GambleExchangeService::perform().
     */
    public function isPermanent(Player $player, Reputation $reputation): bool
    {
        return $this->hasCompletedTopTierFeat($player, $reputation)
            || $this->hasCurrentTierMedal($player, $reputation);
    }

    private function hasCompletedTopTierFeat(Player $player, Reputation $reputation): bool
    {
        $topTier = $reputation->tiers->sortByDesc('min_points')->first();

        return $topTier !== null
            && $topTier->feat_quest_id !== null
            && $this->reputationService->isFeatCompleted($player, $topTier);
    }

    private function hasCurrentTierMedal(Player $player, Reputation $reputation): bool
    {
        $playerReputation = $this->reputationService->getOrCreate($player, $reputation);
        $currentTier = $this->reputationService->getCurrentTier($reputation, $playerReputation->points);

        return $currentTier !== null && $currentTier->medal_name !== null;
    }

    private function resolveOne(Player $player, Reputation $reputation): ?array
    {
        $type = $reputation->favor_effect_type;
        if (! $type instanceof DivineFavorType) {
            return null;
        }

        $chance = (int) ($reputation->favor_proc_chance ?? 1);
        $topTier = $reputation->tiers->sortByDesc('min_points')->first();

        // Подвиг верхнего тира (если выполнен) даёт итоговый максимум навсегда —
        // выше приоритетом, чем обычная медаль того же тира.
        if ($this->hasCompletedTopTierFeat($player, $reputation)) {
            return [
                'reputation' => $reputation,
                'type' => $type,
                'chance' => $chance,
                'percent' => (int) ($topTier->feat_favor_percent ?? 0),
                'durationSeconds' => (int) ($topTier->favor_duration_seconds ?? self::DEFAULT_FAVOR_DURATION_SECONDS),
                'permanent' => true,
            ];
        }

        $playerReputation = $this->reputationService->getOrCreate($player, $reputation);
        $currentTier = $this->reputationService->getCurrentTier($reputation, $playerReputation->points);

        if ($currentTier === null || $currentTier->favor_percent === null) {
            return null;
        }

        // Обычная медаль текущего тира уже получена (у тира есть medal_name,
        // т.е. это не самый первый безмедальный тир) — % этого тира закрепляется
        // навсегда, эликсир для него больше не нужен.
        if ($currentTier->medal_name !== null) {
            return [
                'reputation' => $reputation,
                'type' => $type,
                'chance' => $chance,
                'percent' => (int) $currentTier->favor_percent,
                'durationSeconds' => (int) ($currentTier->favor_duration_seconds ?? self::DEFAULT_FAVOR_DURATION_SECONDS),
                'permanent' => true,
            ];
        }

        // Самый первый (безмедальный) тир — эффект только пока активен эликсир.
        if ($reputation->favor_marker_effect_id === null || ! $this->hasActiveMarker($player, $reputation->favor_marker_effect_id)) {
            return null;
        }

        return [
            'reputation' => $reputation,
            'type' => $type,
            'chance' => $chance,
            'percent' => (int) $currentTier->favor_percent,
            'durationSeconds' => (int) ($currentTier->favor_duration_seconds ?? self::DEFAULT_FAVOR_DURATION_SECONDS),
            'permanent' => false,
        ];
    }

    private function hasActiveMarker(Player $player, int $effectId): bool
    {
        return PlayerActiveEffect::where('player_id', $player->id)
            ->where('effect_id', $effectId)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}
