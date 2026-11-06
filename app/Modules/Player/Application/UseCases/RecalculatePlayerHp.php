<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases;

use App\Modules\Player\Application\DTOs\PlayerHpRecalculation;
use App\Modules\Player\Domain\Services\PlayerStatFormulas;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class RecalculatePlayerHp
{
    public function execute(int $playerId, bool $dryRun = false): ?PlayerHpRecalculation
    {
        $ownerId = Player::whereKey($playerId)->value('user_id');

        return DB::transaction(function () use ($playerId, $ownerId, $dryRun): ?PlayerHpRecalculation {
            $user = $ownerId === null ? null : User::without('player')->whereKey($ownerId)->lockForUpdate()->first();
            $player = Player::whereKey($playerId)->lockForUpdate()->first();
            if ($player === null || $player->user_id != $ownerId) {
                return null;
            }
            $newHpMax = PlayerStatFormulas::DEFAULT_HP
                + PlayerStatFormulas::HP_PER_LEVEL * (max(1, (int) $player->lvl) - 1)
                + PlayerStatFormulas::HP_PER_ENDURANCE * max(0, (int) $player->getEndurance() - 1);
            $oldHpMax = (int) $player->hp_max;
            $oldHpNow = (int) $player->hp_now;
            $newHpNow = $oldHpNow <= 0 ? 0 : ($oldHpMax > 0
                ? max(1, min($newHpMax, (int) round($oldHpNow * $newHpMax / $oldHpMax)))
                : $newHpMax);
            if (! $dryRun) {
                $player->hp_max = $newHpMax;
                $player->hp_now = $newHpNow;
                // Player::saving resets the regeneration baseline when HP or its maximum changes.
                $player->save();
            }

            return new PlayerHpRecalculation(
                name: $user?->name ?? '#'.$playerId,
                level: (int) $player->lvl,
                oldHpMax: $oldHpMax,
                newHpMax: $newHpMax,
                oldHpNow: $oldHpNow,
                newHpNow: $newHpNow,
            );
        }, attempts: 3);
    }
}
