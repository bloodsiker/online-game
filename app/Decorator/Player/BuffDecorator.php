<?php

namespace App\Decorator\Player;

class BuffDecorator extends PlayerDecorator
{
    public function getLeftHandMinDmg(): int
    {
        return $this->player->getLeftHandMinDmg();
    }

    public function getLeftHandMaxDmg(): int
    {
        return $this->player->getLeftHandMaxDmg();
    }

    public function getRightHandMinDmg():int
    {
        return $this->player->getRightHandMinDmg();
    }

    public function getRightHandMaxDmg(): int
    {
        return $this->player->getRightHandMaxDmg();
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
        return $this->player->getAgility(); // Buff does not affect agility
    }

    public function getMud() {
        return $this->player->getMud();
    }

    public function getIntelligence() {
        return $this->player->getIntelligence(); //Buff does not affect intelligence
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
