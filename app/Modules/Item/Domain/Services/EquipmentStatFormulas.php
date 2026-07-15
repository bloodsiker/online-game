<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Services;

use App\Listeners\RecalculatePlayerModification;

/**
 * Формулы стат снаряжения по уровню чекпоинта — тот же принцип, что и
 * MonsterStatFormulas: цифры считаются от игровых констант, а не вбиваются
 * вручную. Ориентир — «типичный Танк-билд» (55% бюджета в силу, см.
 * SimFighter/battle:simulate-pve): броня и урон оружия на чекпоинте
 * подобраны так, чтобы итоговый урон/броня такого билда с этим шмотом
 * совпадали с тем, что уже проверено симуляцией (см. StarterEquipmentSeeder
 * докблок — числа level20 воспроизводятся формулой один в один).
 *
 * ВАЖНО: формула откалибрована и проверена от уровня ~10 и выше (там, где
 * weaponScale начинает расти); ниже она не совпадает с вручную подобранными
 * числами Тира1 (1-20) — тир1 намеренно оставлен как есть, формула
 * используется только для Тира2+ (чекпоинты 20+).
 */
final class EquipmentStatFormulas
{
    private const BASE_MIN_DMG = 10.0;

    private const BASE_MAX_DMG = 15.0;

    /** Базовый урон кулаков (см. PlayerFactory) — вычитается из формулы, чтобы получить бонус ОРУЖИЯ */
    private const FIST_MIN_DMG = 1;

    private const FIST_MAX_DMG = 2;

    /** Доля бюджета стат, которую типичный Танк вкладывает в силу (см. SimFighter) */
    private const TANK_STR_SHARE = 0.55;

    /** Доля брони от чистой силы Танка, которую суммарно даёт весь комплект брони */
    private const ARMOR_BUDGET_SHARE = 0.5;

    public static function weaponScale(int $level): float
    {
        return max(1.0, $level / 12);
    }

    public static function typicalTankStrength(int $level): int
    {
        $budget = max(8, 8 * ($level - 1));

        return (int) round($budget * self::TANK_STR_SHARE);
    }

    /**
     * Бонус урона ОРУЖИЯ (сверх кулаков) на уровне.
     *
     * @return array{0: int, 1: int}
     */
    public static function weaponDamage(int $level): array
    {
        $scale = self::weaponScale($level);
        $totalMin = (int) round(self::BASE_MIN_DMG * $scale);
        $totalMax = (int) round(self::BASE_MAX_DMG * $scale);

        return [
            max(1, $totalMin - self::FIST_MIN_DMG),
            max(2, $totalMax - self::FIST_MAX_DMG),
        ];
    }

    /** Базовая броня на один слот при $totalSlots открытых слотах брони на этом уровне. */
    public static function armorPerSlot(int $level, int $totalSlots = 9): int
    {
        $str = self::typicalTankStrength($level);
        $totalArmor = self::ARMOR_BUDGET_SHARE * max(0, $str - 1) * RecalculatePlayerModification::ARMOR_PER_STR;

        return max(1, (int) round($totalArmor / $totalSlots));
    }
}
