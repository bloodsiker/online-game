<?php

namespace App\DTO;

use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Battle\BattleRound;
use App\Models\Player\Player;

class FightDTO
{
    protected Battle $battle;
    protected BattleRound $battleRound;
    protected ?BattleDetail $attackedMonster;
    protected Player $player;
    protected bool $isPlayerDead = false;

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
}
