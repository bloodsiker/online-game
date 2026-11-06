<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Player\Application\UseCases\RecalculatePlayerHp;
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

    public function handle(RecalculatePlayerHp $recalculate): int
    {
        $dry = (bool) $this->option('dry-run');

        Player::query()->select('id')->chunkById(200, function ($players) use ($dry, $recalculate): void {
            foreach ($players as $player) {
                $result = $recalculate->execute(playerId: (int) $player->id, dryRun: $dry);
                if ($result === null) {
                    continue;
                }

                $this->line(sprintf(
                    '%s (lvl %d): hp_max %d → %d, hp_now %d → %d%s',
                    $result->name,
                    $result->level,
                    $result->oldHpMax,
                    $result->newHpMax,
                    $result->oldHpNow,
                    $result->newHpNow,
                    $dry ? ' [dry-run]' : '',
                ));

            }
        });

        return self::SUCCESS;
    }
}
