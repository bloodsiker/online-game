<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Battle\Domain\Enums\CombatClass;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Domain\DTO\StatModifier;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerItemBuff;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;

class PlayerStatService
{
    /** @var array<string, StatSheet> */
    private array $resolvedSheets = [];

    public function __construct(
        private readonly PlayerEquipmentLoader $equipmentLoader,
    ) {}

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
        $cacheKey = $this->cacheKey($player);
        if (isset($this->resolvedSheets[$cacheKey])) {
            return $this->resolvedSheets[$cacheKey];
        }

        $this->equipmentLoader->load($player);

        $modifiers = [
            ...$this->fromEquipment($player),
            ...$this->fromPassiveSkills($player),
            ...$this->fromBuffs($player),
            ...$this->fromActiveEffects($player),
        ];

        return $this->resolvedSheets[$cacheKey] = $this->buildSheet($player, $modifiers);
    }

    public function invalidate(Player|int|null $player = null): void
    {
        if ($player === null) {
            $this->resolvedSheets = [];

            return;
        }

        $cacheKey = $player instanceof Player
            ? $this->cacheKey($player)
            : 'player:'.$player;

        unset($this->resolvedSheets[$cacheKey]);
    }

    private function cacheKey(Player $player): string
    {
        return $player->getKey() !== null
            ? 'player:'.$player->getKey()
            : 'object:'.spl_object_id($player);
    }

    // -------------------------------------------------------------------------
    // Sheet assembly
    // -------------------------------------------------------------------------

    /**
     * Двухпроходный расчёт: сначала итоговые первичные статы (с учётом
     * модификаторов шмота/баффов), затем производные — уворот/крит/броня/
     * критурон/бонус урона считаются уже от итоговых первичных. Иначе
     * «+ловкость» на предмете не давала уворота.
     */
    private function buildSheet(Player $player, array $modifiers): StatSheet
    {
        // ── Проход 1: первичные статы ────────────────────────────────────────
        $primaryBase = [
            'strength' => (float) floor($player->strength),
            'intuition' => (float) floor($player->intuition),
            'agility' => (float) floor($player->agility),
            'wisdom' => (float) floor($player->wisdom),
            'intelligence' => (float) floor($player->intelligence),
            'endurance' => (float) floor($player->endurance),
        ];

        $primary = $this->applyModifiers($primaryBase, $modifiers);

        // ── Проход 2: производные от итоговых первичных ─────────────────────
        $derivedBase = [
            'dodge' => (float) max(0, ($primary['agility'] - 1) * PlayerStatFormulas::DODGE_PER_AGILITY),
            'critical' => (float) max(0, ($primary['intuition'] - 1) * PlayerStatFormulas::CRITICAL_PER_INT),
            'armor' => (float) max(0, ($primary['strength'] - 1) * PlayerStatFormulas::ARMOR_PER_STR),
            'magic_resistance' => (float) max(0, ($primary['wisdom'] - 1) * PlayerStatFormulas::MAGIC_RESIST_PER_WIS),
            // Как и броня от силы — от итоговой выносливости (с учётом шмота/баффов),
            // а не от «сырого» hp_max: иначе +выносливость с предмета не давала бы HP.
            'hp_max' => (float) (PlayerStatFormulas::DEFAULT_HP
                + PlayerStatFormulas::HP_PER_LEVEL * (max(1, (int) $player->lvl) - 1)
                + PlayerStatFormulas::HP_PER_ENDURANCE * max(0, $primary['endurance'] - 1)),
            'mp_max' => (float) $player->mp_max,
            'left_min_dmg' => (float) $player->min_dmg,
            'left_max_dmg' => (float) $player->max_dmg,
            'right_min_dmg' => (float) $player->min_dmg,
            'right_max_dmg' => (float) $player->max_dmg,
            'magic_attack' => 0.0,
            // Как и блок щитом — целиком с экипировки (жезл), базы своей нет
            'magic_critical' => 0.0,
            // Критурон растёт от итоговой интуиции: у каждой первичной статы двойная ценность
            'crit_damage' => PlayerStatFormulas::CRIT_DAMAGE_BASE
                + PlayerStatFormulas::critDamageBonus((float) $primary['intuition'], max(1, (int) $player->lvl)),
            // Блок щитом — целиком от предметов (щит), базы своей нет
            'block_chance' => 0.0,
            'block_flat' => 0.0,
            'block_percent' => 0.0,
        ];

        // Сила усиливает урон оружия процентом — от итоговой силы, с мягким потолком
        $strengthDmgPct = PlayerStatFormulas::strengthDamagePercent(
            (float) $primary['strength'],
            max(1, (int) $player->lvl),
        );
        foreach (['left_min_dmg', 'left_max_dmg', 'right_min_dmg', 'right_max_dmg'] as $dmgStat) {
            $modifiers[] = new StatModifier(stat: $dmgStat, value: $strengthDmgPct, isPercent: true, source: 'strength');
        }

        $computed = $primary + $this->applyModifiers($derivedBase, $modifiers);

        $sheet = new StatSheet;
        $sheet->modifiers = $modifiers;
        $sheet->freeStats = $player->free_stats;
        $sheet->strength = $computed['strength'];
        $sheet->intuition = $computed['intuition'];
        $sheet->agility = $computed['agility'];
        $sheet->baseStrength = (int) $primaryBase['strength'];
        $sheet->baseAgility = (int) $primaryBase['agility'];
        $sheet->baseIntuition = (int) $primaryBase['intuition'];
        $sheet->wisdom = $computed['wisdom'];
        $sheet->intelligence = $computed['intelligence'];
        $sheet->endurance = $computed['endurance'];
        $sheet->dodge = $computed['dodge'];
        $sheet->critical = $computed['critical'];
        $sheet->armor = $computed['armor'];
        $sheet->magicResistance = $computed['magic_resistance'];
        $sheet->hpMax = $computed['hp_max'];
        $sheet->mpMax = $computed['mp_max'];
        $sheet->leftMinDmg = $computed['left_min_dmg'];
        $sheet->leftMaxDmg = $computed['left_max_dmg'];
        $sheet->rightMinDmg = $computed['right_min_dmg'];
        $sheet->rightMaxDmg = $computed['right_max_dmg'];
        $sheet->magicAttack = $computed['magic_attack'];
        $sheet->magicCritical = $computed['magic_critical'];
        $sheet->critDamage = $computed['crit_damage'];
        $sheet->blockChance = $computed['block_chance'];
        $sheet->blockFlat = $computed['block_flat'];
        $sheet->blockPercent = $computed['block_percent'];
        $sheet->level = max(1, (int) $player->lvl);
        $sheet->combatClass = $this->determineCombatClass($player);

        return $sheet;
    }

    /**
     * base + flat модификаторы, затем процентные: floor((base+flat)×(1+pct/100)).
     *
     * @param  array<string, float>  $base
     * @param  StatModifier[]  $modifiers
     * @return array<string, int>
     */
    private function applyModifiers(array $base, array $modifiers): array
    {
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

        $computed = [];
        foreach ($base as $stat => $baseVal) {
            $computed[$stat] = max(0, (int) floor(($baseVal + $flat[$stat]) * (1 + $percent[$stat] / 100)));
        }

        return $computed;
    }

    // -------------------------------------------------------------------------
    // Modifier sources
    // -------------------------------------------------------------------------

    /** @return StatModifier[] */
    private function fromEquipment(Player $player): array
    {
        $modifiers = [];
        $equip = $this->equipmentLoader->load($player);

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

            // Камень/руна с общим статом 'attack' усиливает урон только того
            // оружия, в котором физически стоит — не обе руки разом.
            $weaponSide = match ($item->id) {
                optional($equip->handLeft)->id => 'left',
                optional($equip->handRight)->id => 'right',
                default => null,
            };

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
                    ShareItemStatType::MAGIC_RESISTANCE => 'magic_resistance',
                    ShareItemStatType::MAGIC_CRITICAL => 'magic_critical',
                    ShareItemStatType::CRIT_DAMAGE => 'crit_damage',
                    ShareItemStatType::ENDURANCE => 'endurance',
                    ShareItemStatType::BLOCK_CHANCE => 'block_chance',
                    ShareItemStatType::BLOCK_FLAT => 'block_flat',
                    ShareItemStatType::BLOCK_PERCENT => 'block_percent',
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

            // Upgrade bonus: each +1 on an armor slot adds 5% to THIS item's own armor value
            // (flat, not percent — a percent modifier here would pool with every other
            // equipped armor piece's upgrade bonus onto the single shared 'armor' stat,
            // see applyModifiers(), making the total scale with slot count instead of gear).
            $upgradeLvl = $item->upgrade_lvl ?? 0;
            if ($upgradeLvl > 0 && in_array($item->itemInfo->slot, $armorSlots, true)) {
                $itemArmorFlat = 0.0;
                foreach ($item->itemInfo->stats as $stat) {
                    if ($stat->stat_type === ShareItemStatType::ARMOR && $stat->value_type !== ItemEffectValueType::PERCENT) {
                        $itemArmorFlat += $stat->value;
                    }
                }
                if ($itemArmorFlat > 0) {
                    $modifiers[] = new StatModifier(
                        stat: 'armor',
                        value: $itemArmorFlat * $upgradeLvl * 0.05,
                        isPercent: false,
                        source: sprintf('upgrade:+%d %s', $upgradeLvl, $item->itemInfo->name),
                    );
                }
            }

            // Gem bonuses from socketed gems
            foreach ($item->gems as $itemGem) {
                $gemStats = $itemGem->gemInfo->gem_stats ?? [];
                foreach ($gemStats as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }
                    array_push($modifiers, ...$this->equipmentStatModifiers($entry, 'gem:'.$itemGem->gemInfo->name, $weaponSide));
                }
            }

            // Rune bonuses from imbued runes
            foreach ($item->runes as $itemRune) {
                foreach ($itemRune->stats as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }
                    array_push($modifiers, ...$this->equipmentStatModifiers($entry, 'rune:'.$itemRune->runeInfo->name, $weaponSide));
                }
            }
        }

        // Weapon damage — flat modifiers that replace the zero base
        if ($equip->handLeft instanceof Item) {
            $source = 'equipment:'.$equip->handLeft->itemInfo->name;
            $weaponMinDamage = 0.0;
            $weaponMaxDamage = 0.0;

            foreach ($equip->handLeft->itemInfo->stats as $stat) {
                if ($stat->stat_type === ShareItemStatType::ATTACK_MIN) {
                    $weaponMinDamage += $stat->value;
                    $modifiers[] = new StatModifier('left_min_dmg', $stat->value, false, $source);
                } elseif ($stat->stat_type === ShareItemStatType::ATTACK_MAX) {
                    $weaponMaxDamage += $stat->value;
                    $modifiers[] = new StatModifier('left_max_dmg', $stat->value, false, $source);
                }
            }

            // Each +1 adds 5% of THIS weapon's own damage. The bonus is flat so
            // it does not multiply base player damage, gems, runes or flat buffs.
            $upgradeLvl = $equip->handLeft->upgrade_lvl ?? 0;
            if ($upgradeLvl > 0) {
                $upgradeSource = sprintf('upgrade:+%d %s', $upgradeLvl, $equip->handLeft->itemInfo->name);
                $modifiers[] = new StatModifier('left_min_dmg', $weaponMinDamage * $upgradeLvl * 0.05, false, $upgradeSource);
                $modifiers[] = new StatModifier('left_max_dmg', $weaponMaxDamage * $upgradeLvl * 0.05, false, $upgradeSource);
            }
        }

        if ($equip->handRight instanceof Item) {
            $source = 'equipment:'.$equip->handRight->itemInfo->name;
            $weaponMinDamage = 0.0;
            $weaponMaxDamage = 0.0;

            foreach ($equip->handRight->itemInfo->stats as $stat) {
                if ($stat->stat_type === ShareItemStatType::ATTACK_MIN) {
                    $weaponMinDamage += $stat->value;
                    $modifiers[] = new StatModifier('right_min_dmg', $stat->value, false, $source);
                } elseif ($stat->stat_type === ShareItemStatType::ATTACK_MAX) {
                    $weaponMaxDamage += $stat->value;
                    $modifiers[] = new StatModifier('right_max_dmg', $stat->value, false, $source);
                }
            }

            // Same isolated weapon-only bonus for the right hand.
            $upgradeLvl = $equip->handRight->upgrade_lvl ?? 0;
            if ($upgradeLvl > 0) {
                $upgradeSource = sprintf('upgrade:+%d %s', $upgradeLvl, $equip->handRight->itemInfo->name);
                $modifiers[] = new StatModifier('right_min_dmg', $weaponMinDamage * $upgradeLvl * 0.05, false, $upgradeSource);
                $modifiers[] = new StatModifier('right_max_dmg', $weaponMaxDamage * $upgradeLvl * 0.05, false, $upgradeSource);
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
        // Камни пишут ключ 'stat', эффекты — 'type'; принимаем оба
        $type = $entry['type'] ?? $entry['stat'] ?? null;
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
     * Как modifiersFromEntry(), но для гема/руны, физически вставленной в
     * конкретный слот руки (см. fromEquipment()): общий стат 'attack' бьёт
     * только по этой руке, а не по обеим сразу, как это происходит для
     * баффов/эффектов без привязки к предмету.
     *
     * @return StatModifier[]
     */
    private function equipmentStatModifiers(array $entry, string $source, ?string $weaponSide): array
    {
        $type = $entry['type'] ?? $entry['stat'] ?? null;

        if (! $type) {
            return [];
        }

        if ($weaponSide !== null && $this->normalizeStatKey($type) === 'attack') {
            $value = (float) ($entry['value'] ?? 0);
            $isPct = (bool) ($entry['is_percent'] ?? false);

            return [
                new StatModifier($weaponSide.'_min_dmg', $value, $isPct, $source),
                new StatModifier($weaponSide.'_max_dmg', $value, $isPct, $source),
            ];
        }

        return $this->modifiersFromEntry($entry, $source);
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
            'defense' => 'armor',
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
