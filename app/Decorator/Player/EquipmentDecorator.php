<?php

namespace App\Decorator\Player;

use App\Enums\ItemEffectType;
use App\Enums\ItemEffectValueType;
use App\Models\Item\Item;

class EquipmentDecorator extends PlayerDecorator
{
//    private function getEffectValue(ItemEffectType $type): int
//    {
//        return $this->equippedItems()->sum(function (Item $item) use ($type) {
//            return $item->itemInfo
//                ->effects
//                ->where('effect_type', $type)
//                ->sum('value');
//        });
//    }

    private function getEffectValue(ItemEffectType $type): int
    {
        $flat = 0;
        $percent = 0;

        foreach ($this->equippedItems() as $item) {
            foreach ($item->itemInfo->effects as $effect) {
                if ($effect->effect_type !== $type) {
                    continue;
                }

                if ($effect->value_type === ItemEffectValueType::FLAT) {
                    $flat += $effect->value;
                }

                if ($effect->value_type === ItemEffectValueType::PERCENT) {
                    $percent += $effect->value;
                }
            }
        }

        return (int) ($flat + ($this->player->getArmor() * $percent / 100));
    }

    private function getWeaponDamage(?Item $weapon, ItemEffectType $type, int $baseDamage): int
    {
        if (!$weapon) {
            return $baseDamage;
        }

        $damage = $weapon->itemInfo
            ->effects
            ->where('effect_type', $type)
            ->sum('value');

        return $damage;
    }

    public function getLeftHandMinDmg(): int
    {
//        $baseAttack = $this->player->getLeftHandMinDmg();
//        if ($this->player->playerEquip->handLeft instanceof Item) {
//            $baseAttack = $this->player->playerEquip->handLeft->itemInfo->min_attack;
//        }
//
//        return $baseAttack;

        return $this->getWeaponDamage(
            weapon: $this->player->playerEquip->handLeft,
            type: ItemEffectType::ATTACK_MIN,
            baseDamage: $this->player->getLeftHandMinDmg()
        );
    }

    public function getLeftHandMaxDmg(): int
    {
//        $baseAttack = $this->player->getLeftHandMaxDmg();
//        if ($this->player->playerEquip->handLeft instanceof Item) {
//            $baseAttack = $this->player->playerEquip->handLeft->itemInfo->max_attack;
//        }
//
//        return $baseAttack;

        return $this->getWeaponDamage(
            weapon: $this->player->playerEquip->handLeft,
            type: ItemEffectType::ATTACK_MAX,
            baseDamage: $this->player->getLeftHandMaxDmg()
        );
    }

    public function getRightHandMinDmg(): int
    {
//        $baseAttack = $this->player->getRightHandMinDmg();
//        $rightHand = $this->player->playerEquip->handRight;
//        if ($rightHand instanceof Item && $rightHand->itemInfo->type === 'weapon') {
//            $baseAttack = $rightHand->itemInfo->min_attack;
//        }
//
//        return $baseAttack;

        return $this->getWeaponDamage(
            weapon: $this->player->playerEquip->handRight,
            type: ItemEffectType::ATTACK_MIN,
            baseDamage: $this->player->getRightHandMinDmg()
        );
    }

    public function getRightHandMaxDmg(): int
    {
//        $baseAttack = $this->player->getRightHandMaxDmg();
//        $rightHand = $this->player->playerEquip->handRight;
//        if ($rightHand instanceof Item && $rightHand->itemInfo->type === 'weapon') {
//            $baseAttack = $rightHand->itemInfo->max_attack;
//        }
//
//        return $baseAttack;

        return $this->getWeaponDamage(
            weapon: $this->player->playerEquip->handRight,
            type: ItemEffectType::ATTACK_MAX,
            baseDamage: $this->player->getRightHandMaxDmg()
        );
    }

    public function getArmor(): int
    {
//        return $this->player->getArmor() + $this->getArmorFromEquipment();
        return $this->player->getArmor() + $this->getEffectValue(ItemEffectType::ARMOR);
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
