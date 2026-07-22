<?php

namespace App\Modules\Battle\Application\DTOs;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

final class FightDTO
{
    protected Battle $battle;

    protected BattleRound $battleRound;

    protected ?BattleDetail $attackedMonster;

    protected Player $player;

    protected bool $isPlayerDead = false;

    /** Разовые уведомления (квесты и т.п.) — не сохраняются в БД, см. AttackResultDTO::getSideLog() */
    protected string $sideLog = '';

    public function getBattle(): Battle
    {
        return $this->battle;
    }

    public function setBattle(Battle $battle): self
    {
        $this->battle = $battle;

        return $this;
    }

    public function getBattleRound(): BattleRound
    {
        return $this->battleRound;
    }

    public function setBattleRound(BattleRound $battleRound): self
    {
        $this->battleRound = $battleRound;

        return $this;
    }

    public function getAttackedMonster(): ?BattleDetail
    {
        return $this->attackedMonster;
    }

    public function setAttackedMonster(?BattleDetail $attackedMonster): self
    {
        $this->attackedMonster = $attackedMonster;

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function isPlayerDead(): bool
    {
        return $this->isPlayerDead;
    }

    public function setIsPlayerDead(bool $isPlayerDead): self
    {
        $this->isPlayerDead = $isPlayerDead;

        return $this;
    }

    public function getSideLog(): string
    {
        return $this->sideLog;
    }

    public function setSideLog(string $sideLog): self
    {
        $this->sideLog = $sideLog;

        return $this;
    }
}
