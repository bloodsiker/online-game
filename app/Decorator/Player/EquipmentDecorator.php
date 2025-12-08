<?php

namespace App\Decorator\Player;

use App\Models\Item\Item;

class EquipmentDecorator extends PlayerDecorator
{
    public function getLeftHandMinDmg(): int
    {
        $baseAttack = $this->player->getLeftHandMinDmg();
        if ($this->player->playerEquip->handLeft instanceof Item) {
            $baseAttack = $this->player->playerEquip->handLeft->itemInfo->min_attack;
        }

        return $baseAttack;
    }

    public function getLeftHandMaxDmg(): int
    {
        $baseAttack = $this->player->getLeftHandMaxDmg();
        if ($this->player->playerEquip->handLeft instanceof Item) {
            $baseAttack = $this->player->playerEquip->handLeft->itemInfo->max_attack;
        }

        return $baseAttack;
    }

    public function getRightHandMinDmg(): int
    {
        $baseAttack = $this->player->getRightHandMinDmg();
        $rightHand = $this->player->playerEquip->handRight;
        if ($rightHand instanceof Item && $rightHand->itemInfo->type === 'weapon') {
            $baseAttack = $rightHand->itemInfo->min_attack;
        }

        return $baseAttack;
    }

    public function getRightHandMaxDmg(): int
    {
        $baseAttack = $this->player->getRightHandMaxDmg();
        $rightHand = $this->player->playerEquip->handRight;
        if ($rightHand instanceof Item && $rightHand->itemInfo->type === 'weapon') {
            $baseAttack = $rightHand->itemInfo->max_attack;
        }

        return $baseAttack;
    }

    public function getArmor(): int
    {
        return $this->player->getArmor() + $this->getArmorFromEquipment();
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

    protected function getArmorFromEquipment(): int
    {
        $playerEquip = $this->player->playerEquip;
        $armor = 0;
        if ($playerEquip->helmet) {
            $armor += $playerEquip->helmetSlot->itemInfo->armor;
        }

        if ($playerEquip->shoulder) {
            $armor += $playerEquip->shoulderSlot->itemInfo->armor;
        }

        if ($playerEquip->forearm) {
            $armor += $playerEquip->forearmSlot->itemInfo->armor;
        }

        if ($playerEquip->armor) {
            $armor += $playerEquip->armorSlot->itemInfo->armor;
        }

        if ($playerEquip->legging) {
            $armor += $playerEquip->leggingSlot->itemInfo->armor;
        }

        if ($playerEquip->chain_armor) {
            $armor += $playerEquip->chainArmorSlot->itemInfo->armor;
        }

        if ($playerEquip->cloak) {
            $armor += $playerEquip->cloakSlot->itemInfo->armor;
        }

        if ($playerEquip->shoes) {
            $armor += $playerEquip->shoesSlot->itemInfo->armor;
        }

        if ($playerEquip->gloves) {
            $armor += $playerEquip->glovesSlot->itemInfo->armor;
        }

        return $armor;
    }
}
