<?php

namespace App\Services\Combat\Boss;

use App\DTO\AttackResultDTO;
use App\Models\Battle\Battle;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class BossFightContext
{
    public function __construct(
        private MonsterOnLocation $locationMonster,
        private Battle $battle,
        private Player $player,
        private AttackResultDTO $result
    ) {}

    public function getLocationMonster(): MonsterOnLocation
    {
        return $this->locationMonster;
    }

    public function getBattle(): Battle
    {
        return $this->battle;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getResult(): AttackResultDTO
    {
        return $this->result;
    }

    public function getBossCurrentHp(): int
    {
        return $this->locationMonster->hp_now;
    }

    public function getBossMaxHp(): int
    {
        return $this->locationMonster->monster->hp;
    }

    public function getBossHpPercent(): float
    {
        return ($this->getBossCurrentHp() / $this->getBossMaxHp()) * 100;
    }

    public function getCurrentTurn(): int
    {
        return $this->battle->rounds;
    }

    public function getCurrentPhase(): int
    {
        return $this->locationMonster->current_phase ?? 1;
    }

    public function dealDamageToBoss(int $damage): void
    {
        $this->locationMonster->hp_now = max(0, $this->locationMonster->hp_now - $damage);
    }

    public function healBoss(int $amount): void
    {
        $maxHp = $this->getBossMaxHp();
        $this->locationMonster->hp_now = min($maxHp, $this->locationMonster->hp_now + $amount);
    }

    public function dealDamageToPlayer(int $damage): void
    {
        $this->player->hp_now = max(0, $this->player->hp_now - $damage);
    }

    public function addLog(string $message): void
    {
        $this->result->log($message);
    }

    public function getMechanicData(string $key, $default = null)
    {
        $metadata = $this->battle->boss_metadata ?? [];

        return $metadata['mechanic_data'][$key] ?? $default;
    }

    public function setMechanicData(string $key, $value): void
    {
        $metadata = $this->battle->boss_metadata ?? [];
        $metadata['mechanic_data'][$key] = $value;
        $this->battle->boss_metadata = $metadata;
    }
}
