<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\Player\Domain\Events\PlayerDied;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;

final readonly class PlayerDeathFinalizer
{
    public function __construct(
        private DungeonCoordinator $dungeonCoordinator,
        private PlayerStatService $statService,
    ) {}

    /**
     * Applies the common, persistent consequences of death exactly once for a
     * battle participant. The caller is responsible for locking the player and
     * wrapping this call in a transaction.
     */
    public function finalize(
        Player $player,
        ?BattleDetail $participant,
        AttackResultDTO $result,
    ): bool {
        if ($participant !== null) {
            if ($participant->status->isDeath()) {
                return false;
            }

            // Mark the participant first. A concurrent heartbeat cannot apply
            // the experience penalty twice; a failed transaction rolls it back.
            $participant->status = 0;
            $participant->save();
        }

        event(new PlayerDied($player));

        PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->delete();
        $this->statService->invalidate($player);

        $result->log('<p>Вы <font color="red"><b>проиграли</b></font>! Опыт -10%.</p>');

        $dungeonDeathMessage = $this->dungeonCoordinator->handlePlayerDeath($player);
        if ($dungeonDeathMessage !== null) {
            $result->log('<p><b>'.$dungeonDeathMessage.'</b></p>');
        }

        return true;
    }
}
