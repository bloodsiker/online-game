<?php

namespace App\Services\Combat;

use App\DTO\AttackResultDTO;
use App\DTO\FightDTO;
use App\Events\PlayerDied;
use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Battle\BattleRound;
use App\Models\Player\Player;
use App\Models\Player\PlayerActiveEffect;

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
