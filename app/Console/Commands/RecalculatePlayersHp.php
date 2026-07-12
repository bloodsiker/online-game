<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Listeners\RecalculatePlayerModification;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Console\Command;

/**
 * Разовый пересчёт hp_max по новой формуле (HP от уровня и выносливости, а не от силы).
 * hp_now масштабируется пропорционально, чтобы никого не убить и не вылечить.
 */
class RecalculatePlayersHp extends Command
{
    protected $signature = 'player:recalc-hp {--dry-run : Показать изменения без записи}';

    protected $description = 'Пересчитать hp_max всех игроков по формуле «HP от уровня и выносливости»';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        Player::query()->with('user')->chunkById(200, function ($players) use ($dry): void {
            foreach ($players as $player) {
                $newHpMax = RecalculatePlayerModification::DEFAULT_HP
                    + RecalculatePlayerModification::HP_PER_LEVEL * (max(1, (int) $player->lvl) - 1)
                    + RecalculatePlayerModification::HP_PER_ENDURANCE * max(0, $player->getEndurance() - 1);

                $oldHpMax = (int) $player->hp_max;
                $newHpNow = $oldHpMax > 0
                    ? min($newHpMax, (int) round($player->hp_now * $newHpMax / $oldHpMax))
                    : $newHpMax;

                $this->line(sprintf(
                    '%s (lvl %d): hp_max %d → %d, hp_now %d → %d%s',
                    $player->user?->name ?? ('#'.$player->id),
                    $player->lvl,
                    $oldHpMax,
                    $newHpMax,
                    $player->hp_now,
                    $newHpNow,
                    $dry ? ' [dry-run]' : '',
                ));

                if (! $dry) {
                    $player->hp_max = $newHpMax;
                    $player->hp_now = $newHpNow;
                    $player->save();
                }
            }
        });

        return self::SUCCESS;
    }
}
