<?php

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\DTOs\FightDTO;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

readonly class PlayerDeathService
{
    public function __construct(
        private PlayerDeathFinalizer $deathFinalizer,
    ) {}

    public function handle(
        Player $player,
        Battle $battle,
        BattleRound $round,
        BattleDetail $attackedPlayer,
        BattleDetail $attackedMonster,
        AttackResultDTO $result
    ): FightDTO {
        $this->deathFinalizer->finalize($player, $attackedPlayer, $result);

        $round->action = $result->getLog();
        $round->save();

        return (new FightDTO)
            ->setBattle($battle)
            ->setIsPlayerDead(true)
            ->setBattleRound($round)
            ->setAttackedMonster($attackedMonster)
            ->setPlayer($player->refresh())
            ->setSideLog($result->getSideLog());
    }
}
