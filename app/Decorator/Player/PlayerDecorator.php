<?php

namespace App\Decorator\Player;

use App\Services\Combat\FightHitInterface;

abstract class PlayerDecorator implements PlayerInterface, FightHitInterface {

    protected PlayerInterface $player;

    public function __construct(PlayerInterface $player) {
        $this->player = $player;
    }

    public function getLeftHandMinDmg():int
    {
        return $this->player->min_dmg;
    }

    public function getLeftHandMaxDmg():int
    {
        return $this->player->max_dmg;
    }

    public function getRightHandMinDmg():int
    {
        return $this->player->min_dmg;
    }

    public function getRightHandMaxDmg(): int
    {
        return $this->player->max_dmg;
    }

    public function getArmor(): int
    {
        return $this->player->getArmor();
    }

    public function getStrength() {
        return $this->player->getStrength();
    }

    public function getInt() {
        return $this->player->getInt();
    }

    public function getAgility() {
        return $this->player->getAgility();
    }

    public function getMud() {
        return $this->player->getMud();
    }

    public function getIntelligence() {
        return $this->player->getIntelligence();
    }

    public function getDodge(): int
    {
        return $this->player->getDodge();
    }

    public function getCritical(): int
    {
        return $this->player->getCritical();
    }

    public function getFreeStats() {
        return $this->player->getFreeStats();
    }
}
