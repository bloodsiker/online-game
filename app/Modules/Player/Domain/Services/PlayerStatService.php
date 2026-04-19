<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Battle\Domain\Enums\CombatClass;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Listeners\RecalculatePlayerModification;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Domain\DTO\StatModifier;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerItemBuff;

class PlayerStatService
{
    /**
     * Scale hp_now/mp_now proportionally when equipped passive skills change.
     * Call before and after the skill change: pass old max and new max.
     */
    public function scaleHp(Player $player, int $oldHpMax, int $newHpMax, int $oldMpMax, int $newMpMax): void
    {
        if ($oldHpMax > 0 && $oldHpMax !== $newHpMax) {
            $player->hp_now = min($newHpMax, (int) round($player->hp_now * $newHpMax / $oldHpMax));
        }

        if ($oldMpMax > 0 && $oldMpMax !== $newMpMax) {
            $player->mp_now = min($newMpMax, (int) round($player->mp_now * $newMpMax / $oldMpMax));
        }

        $player->save();
    }

    /**
     * Build a fully resolved StatSheet for a player,
     * applying equipment, passive skills and buffs.
     */
    public function resolve(Player $player): StatSheet
    {
        $modifiers = [
            ...$this->fromEquipment($player),
            ...$this->fromPassiveSkills($player),
            ...$this->fromBuffs($player),
            ...$this->fromActiveEffects($player),
        ];

        return $this->buildSheet($player, $modifiers);
    }

    // -------------------------------------------------------------------------
    // Sheet assembly
    // -------------------------------------------------------------------------

    private function buildSheet(Player $player, array $modifiers): StatSheet
    {
        $equip = $player->playerEquip;

        // Base values. Weapon slots: if a weapon is equipped the base is 0
        // (weapon damage comes entirely from the item's ATTACK_MIN/MAX effects).
        $base = [
            'strength' => (float) floor($player->strength),
            'intuition' => (float) floor($player->intuition),
            'agility' => (float) floor($player->agility),
            'wisdom' => (float) floor($player->wisdom),
            'intelligence' => (float) floor($player->intelligence),
            'dodge' => (float) max(0, ($player->agility - 1) * RecalculatePlayerModification::DODGE_PER_AGILITY),
            'critical' => (float) max(0, ($player->intuition - 1) * RecalculatePlayerModification::CRITICAL_PER_INT),
            'armor' => (float) max(0, ($player->strength - 1) * RecalculatePlayerModification::ARMOR_PER_STR),
            'hp_max' => (float) $player->hp_max,
            'mp_max' => (float) $player->mp_max,
            'left_min_dmg' => (float) $player->min_dmg,
            'left_max_dmg' => (float) $player->max_dmg,
            'right_min_dmg' => (float) $player->min_dmg,
            'right_max_dmg' => (float) $player->max_dmg,
            'magic_attack' => (float) $player->intel,
        ];

        // Accumulate flat and percent per stat
        $flat = array_fill_keys(array_keys($base), 0.0);
        $percent = array_fill_keys(array_keys($base), 0.0);

        foreach ($modifiers as $m) {
            if (! array_key_exists($m->stat, $base)) {
                continue;
            }
            if ($m->isPercent) {
                $percent[$m->stat] += $m->value;
            } else {
                $flat[$m->stat] += $m->value;
            }
        }

        // Formula: floor( (base + flat) * (1 + percent / 100) )
        $computed = [];
        foreach ($base as $stat => $baseVal) {
            $computed[$stat] = (int) floor(
                ($baseVal + $flat[$stat]) * (1 + $percent[$stat] / 100)
            );
        }

        $sheet = new StatSheet;
        $sheet->modifiers = $modifiers;
        $sheet->freeStats = $player->free_stats;
        $sheet->strength = $computed['strength'];
        $sheet->intuition = $computed['intuition'];
        $sheet->agility = $computed['agility'];
        $sheet->wisdom = $computed['wisdom'];
        $sheet->intelligence = $computed['intelligence'];
        $sheet->dodge = $computed['dodge'];
        $sheet->critical = $computed['critical'];
        $sheet->armor = $computed['armor'];
        $sheet->hpMax = $computed['hp_max'];
        $sheet->mpMax = $computed['mp_max'];
        $sheet->leftMinDmg = $computed['left_min_dmg'];
        $sheet->leftMaxDmg = $computed['left_max_dmg'];
        $sheet->rightMinDmg = $computed['right_min_dmg'];
        $sheet->rightMaxDmg = $computed['right_max_dmg'];
        $sheet->magicAttack = $computed['magic_attack'];
        $sheet->combatClass = $this->determineCombatClass($player);

        return $sheet;
    }

    // -------------------------------------------------------------------------
    // Modifier sources
    // -------------------------------------------------------------------------

    /** @return StatModifier[] */
    private function fromEquipment(Player $player): array
    {
        $modifiers = [];
        $equip = $player->playerEquip;

        if (! $equip) {
            return $modifiers;
        }

        // All slots for armor/misc effects
        $allSlots = array_filter([
            $equip->handLeft, $equip->handRight,
            $equip->helmetSlot, $equip->shoulderSlot, $equip->forearmSlot,
            $equip->armorSlot, $equip->leggingSlot, $equip->chainArmorSlot,
            $equip->cloakSlot, $equip->shoesSlot, $equip->glovesSlot,
            $equip->beltFirstSlot, $equip->beltSecondSlot,
            $equip->bagFirstSlot, $equip->bagSecondSlot,
        ]);

        $armorSlots = ShareItemSlot::armorSlots();

        foreach ($allSlots as $item) {
            $source = 'equipment:'.$item->itemInfo->name;

            foreach ($item->itemInfo->stats as $stat) {
                $mappedStat = match ($stat->stat_type) {
                    ShareItemStatType::ARMOR => 'armor',
                    ShareItemStatType::HP_MAX => 'hp_max',
                    ShareItemStatType::AGILITY => 'agility',
                    ShareItemStatType::INTUITION => 'intuition',
                    ShareItemStatType::WISDOM => 'wisdom',
                    ShareItemStatType::INTELLIGENCE => 'intelligence',
                    ShareItemStatType::DODGE => 'dodge',
                    ShareItemStatType::CRITICAL => 'critical',
                    ShareItemStatType::MAGIC_ATTACK => 'magic_attack',
                    default => null,
                };

                if ($mappedStat !== null) {
                    $modifiers[] = new StatModifier(
                        stat: $mappedStat,
                        value: $stat->value,
                        isPercent: $stat->value_type === ItemEffectValueType::PERCENT,
                        source: $source,
                    );
                }
            }

            // Upgrade bonus: each +1 on an armor slot adds 5% to armor
            $upgradeLvl = $item->upgrade_lvl ?? 0;
            if ($upgradeLvl > 0 && in_array($item->itemInfo->slot, $armorSlots, true)) {
                $modifiers[] = new StatModifier(
                    stat: 'armor',
                    value: (float) ($upgradeLvl * 5),
                    isPercent: true,
                    source: sprintf('upgrade:+%d %s', $upgradeLvl, $item->itemInfo->name),
                );
            }

            // Gem bonuses from socketed gems
            foreach ($item->gems as $itemGem) {
                $gemStats = $itemGem->gemInfo->gem_stats ?? [];
                foreach ($gemStats as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }
                    array_push($modifiers, ...$this->modifiersFromEntry($entry, 'gem:'.$itemGem->gemInfo->name));
                }
            }

            // Rune bonuses from imbued runes
            foreach ($item->runes as $itemRune) {
                foreach ($itemRune->stats as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }
                    array_push($modifiers, ...$this->modifiersFromEntry($entry, 'rune:'.$itemRune->runeInfo->name));
                }
            }
        }

        // Weapon damage — flat modifiers that replace the zero base
        if ($equip->handLeft instanceof Item) {
            $source = 'equipment:'.$equip->handLeft->itemInfo->name;
            foreach ($equip->handLeft->itemInfo->stats as $stat) {
                match ($stat->stat_type) {
                    ShareItemStatType::ATTACK_MIN => $modifiers[] = new StatModifier('left_min_dmg', $stat->value, false, $source),
                    ShareItemStatType::ATTACK_MAX => $modifiers[] = new StatModifier('left_max_dmg', $stat->value, false, $source),
                    default => null,
                };
            }

            // Upgrade bonus: each +1 on weapon adds 5% to damage
            $upgradeLvl = $equip->handLeft->upgrade_lvl ?? 0;
            if ($upgradeLvl > 0) {
                $upgradePct = (float) ($upgradeLvl * 5);
                $upgradeSource = sprintf('upgrade:+%d %s', $upgradeLvl, $equip->handLeft->itemInfo->name);
                $modifiers[] = new StatModifier('left_min_dmg', $upgradePct, true, $upgradeSource);
                $modifiers[] = new StatModifier('left_max_dmg', $upgradePct, true, $upgradeSource);
            }
        }

        if ($equip->handRight instanceof Item) {
            $source = 'equipment:'.$equip->handRight->itemInfo->name;
            foreach ($equip->handRight->itemInfo->stats as $stat) {
                match ($stat->stat_type) {
                    ShareItemStatType::ATTACK_MIN => $modifiers[] = new StatModifier('right_min_dmg', $stat->value, false, $source),
                    ShareItemStatType::ATTACK_MAX => $modifiers[] = new StatModifier('right_max_dmg', $stat->value, false, $source),
                    default => null,
                };
            }

            // Upgrade bonus: each +1 on weapon adds 5% to damage
            $upgradeLvl = $equip->handRight->upgrade_lvl ?? 0;
            if ($upgradeLvl > 0) {
                $upgradePct = (float) ($upgradeLvl * 5);
                $upgradeSource = sprintf('upgrade:+%d %s', $upgradeLvl, $equip->handRight->itemInfo->name);
                $modifiers[] = new StatModifier('right_min_dmg', $upgradePct, true, $upgradeSource);
                $modifiers[] = new StatModifier('right_max_dmg', $upgradePct, true, $upgradeSource);
            }
        }

        return $modifiers;
    }

    /** @return StatModifier[] */
    private function fromPassiveSkills(Player $player): array
    {
        $modifiers = [];

        $passiveSkills = $player->magicSkills()
            ->where('is_passive', true)
            ->with('skillEffects')
            ->get();

        foreach ($passiveSkills as $skill) {
            $source = 'passive:'.$skill->name;

            // Source 1: magic_skills.effects (JSON array of stat modifier objects)
            foreach ($skill->effects ?? [] as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                array_push($modifiers, ...$this->modifiersFromEntry($entry, $source));
            }

            // Source 2: related Effect records via magic_skill_effects
            foreach ($skill->skillEffects as $effect) {
                foreach ($effect->stat_modifiers ?? [] as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }
                    array_push($modifiers, ...$this->modifiersFromEntry($entry, $source));
                }
            }
        }

        return $modifiers;
    }

    /** @return StatModifier[] */
    private function fromBuffs(Player $player): array
    {
        $modifiers = [];

        $buffs = PlayerItemBuff::where('player_id', $player->id)
            ->where('expires_at', '>', now())
            ->get();

        foreach ($buffs as $buff) {
            $stat = match ($buff->effect_type) {
                ItemEffectType::BUFF_ATTACK => 'attack', // расширяется на все 4 dmg стата
                ItemEffectType::BUFF_DEFENSE => 'armor',
                default => null,
            };

            if ($stat === null) {
                continue;
            }

            $isPercent = $buff->value_type === ItemEffectValueType::PERCENT;
            $source = 'buff:'.$buff->effect_type->value;

            if ($stat === 'attack') {
                array_push($modifiers,
                    new StatModifier('left_min_dmg', (float) $buff->value, $isPercent, $source),
                    new StatModifier('left_max_dmg', (float) $buff->value, $isPercent, $source),
                    new StatModifier('right_min_dmg', (float) $buff->value, $isPercent, $source),
                    new StatModifier('right_max_dmg', (float) $buff->value, $isPercent, $source),
                );
            } else {
                $modifiers[] = new StatModifier($stat, (float) $buff->value, $isPercent, $source);
            }
        }

        return $modifiers;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Parse one modifier entry: {"type": "attack", "value": 2, "is_percent": true}
     * "attack" expands to all 4 damage stats.
     *
     * @return StatModifier[]
     */
    private function modifiersFromEntry(array $entry, string $source): array
    {
        $type = $entry['type'] ?? null;
        $value = (float) ($entry['value'] ?? 0);
        $isPct = (bool) ($entry['is_percent'] ?? false);

        if (! $type) {
            return [];
        }

        $type = $this->normalizeStatKey($type);

        if ($type === 'attack') {
            return [
                new StatModifier('left_min_dmg', $value, $isPct, $source),
                new StatModifier('left_max_dmg', $value, $isPct, $source),
                new StatModifier('right_min_dmg', $value, $isPct, $source),
                new StatModifier('right_max_dmg', $value, $isPct, $source),
            ];
        }

        return [new StatModifier($type, $value, $isPct, $source)];
    }

    /**
     * Normalize legacy or abbreviated stat keys to canonical form.
     */
    private function normalizeStatKey(string $key): string
    {
        return match ($key) {
            'str' => 'strength',
            'int' => 'intuition',
            'agi' => 'agility',
            'mud' => 'wisdom',
            'intel' => 'intelligence',
            default => $key,
        };
    }

    /**
     * Класс определяется доминирующей базовой характеристикой:
     * str → Танк, agil → Уворот, int (интуиция) → Крит.
     */
    private function determineCombatClass(Player $player): CombatClass
    {
        return $player->getCombatClass();
    }

    /** @return StatModifier[] */
    private function fromActiveEffects(Player $player): array
    {
        $modifiers = [];

        $activeEffects = PlayerActiveEffect::where('player_id', $player->id)
            ->whereNotNull('effect_id')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with('effect')
            ->get();

        foreach ($activeEffects as $playerEffect) {
            $effect = $playerEffect->effect;

            if (! $effect || empty($effect->stat_modifiers)) {
                continue;
            }

            $source = 'active_effect:'.$effect->slug;

            foreach ($effect->stat_modifiers as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                array_push($modifiers, ...$this->modifiersFromEntry($entry, $source));
            }
        }

        return $modifiers;
    }
}
