<?php

namespace App\Modules\Battle\Application\Services\Combat;

use App\DTO\AttackResultDTO;
use App\Modules\Battle\Application\DTOs\FightDTO;
use App\Events\PlayerDied;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;

readonly class PlayerDeathService
{
    public function handle(
        Player $player,
        Battle $battle,
        BattleRound $round,
        BattleDetail $attackedPlayer,
        BattleDetail $attackedMonster,
        AttackResultDTO $result
    ): FightDTO {
        event(new PlayerDied($player));

        PlayerActiveEffect::where('player_id', $player->id)->delete();

        $result->log('<p>Вы <font color="red"><b>проиграли</b></font>! Опыт -10%.</p>');

        $round->action = $result->getLog();
        $round->save();

        $attackedPlayer->status = 0;
        $attackedPlayer->save();

        return (new FightDTO)
            ->setBattle($battle)
            ->setIsPlayerDead(true)
            ->setBattleRound($round)
            ->setAttackedMonster($attackedMonster)
            ->setPlayer($player->refresh());
    }
}
