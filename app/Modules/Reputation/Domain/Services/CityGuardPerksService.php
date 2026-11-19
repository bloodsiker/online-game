<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Domain\Services;

use Illuminate\Support\Facades\DB;

/**
 * Постоянные бонусы репутации «Городская стража» по рангу игрока:
 * - скидка у торговцев Нейрина (только монетная часть цены, см. CityShopDiscountService);
 * - «Благословение стражи»: +% урона по монстрам и −% урона от монстров (только PvE).
 *
 * Ранг определяется по min_points тиров репутации (0, 500, 1000 … 5000).
 */
final class CityGuardPerksService
{
    public const REPUTATION_ID = 1;

    /** min_points ранга => [скидка %, благословение %] */
    public const PERKS = [
        0 => [0, 0],
        500 => [2, 2],
        1000 => [4, 3],
        2000 => [6, 4],
        3000 => [8, 5],
        4000 => [10, 6],
        5000 => [12, 8],
    ];

    public function shopDiscountPercent(int $playerId): int
    {
        return $this->perksForPoints($this->points($playerId))[0];
    }

    public function blessingPercent(int $playerId): int
    {
        return $this->perksForPoints($this->points($playerId))[1];
    }

    /** @return array{0: int, 1: int} [скидка %, благословение %] */
    public function perksForPoints(int $points): array
    {
        $perks = self::PERKS[0];
        foreach (self::PERKS as $minPoints => $tierPerks) {
            if ($points >= $minPoints) {
                $perks = $tierPerks;
            }
        }

        return $perks;
    }

    /** Исходящий урон игрока по монстру с учётом благословения. */
    public function boostOutgoingDamage(int $playerId, int $damage): int
    {
        $percent = $this->blessingPercent($playerId);

        return $damage > 0 && $percent > 0
            ? (int) round($damage * (100 + $percent) / 100)
            : $damage;
    }

    /** Входящий урон монстра по игроку с учётом благословения (не ниже 1, если урон был). */
    public function reduceIncomingDamage(int $playerId, int $damage): int
    {
        $percent = $this->blessingPercent($playerId);

        return $damage > 0 && $percent > 0
            ? max(1, (int) round($damage * (100 - $percent) / 100))
            : $damage;
    }

    private function points(int $playerId): int
    {
        return (int) DB::table('player_reputations')
            ->where('player_id', $playerId)
            ->where('reputation_id', self::REPUTATION_ID)
            ->value('points');
    }
}
