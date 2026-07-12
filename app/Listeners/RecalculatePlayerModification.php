<?php

namespace App\Listeners;

use App\Events\PlayerChangeStat;
use App\Modules\Player\Domain\Services\PlayerStatService;

class RecalculatePlayerModification
{
    public const DEFAULT_HP = 10;

    public const DEFAULT_MP = 10;

    /**
     * HP растёт от уровня, а не от силы: HP-монополия силы ломала
     * треугольник танк/уворот/крит (сила давала бы HP+броню+урон разом).
     */
    public const HP_PER_LEVEL = 12;

    /**
     * Выносливость — отдельная стата, отвечающая только за HP поверх уровневой
     * базы: осознанный трейдофф «живучесть vs сила класса», а не побочный
     * эффект прокачки боевой статы (см. HP_PER_LEVEL).
     */
    public const HP_PER_ENDURANCE = 3;

    public const MP_PER_MUD = 3;

    public const DODGE_PER_AGILITY = 1;

    public const CRITICAL_PER_INT = 1;

    public const ARMOR_PER_STR = 1;

    /** Прибавка к урону оружия за очко силы, % (80 силы → +20% урона) */
    public const DMG_PCT_PER_STR = 0.35;

    /**
     * Мягкий потолок бонуса урона от силы, %: рост асимптотически ограничен,
     * иначе на высоких уровнях сила давала бы сотни процентов урона при
     * капнутых шансах уворота и критурона у других классов.
     */
    public const DMG_PCT_CAP = 100.0;

    /**
     * Бонус урона от силы, нормированный по уровню: «типичный танк» любого
     * уровня получает одинаковый процент (иначе линейный рост стат к 500 lvl
     * раздувал бы множитель), плюс мягкий потолок как страховка.
     */
    public static function strengthDamagePercent(float $strength, int $level): float
    {
        $levelScale = max(1.0, $level / 12);
        $raw = max(0, ($strength - 1) * self::DMG_PCT_PER_STR) / $levelScale;

        return self::DMG_PCT_CAP * $raw / ($raw + self::DMG_PCT_CAP);
    }

    /** Базовый множитель критического урона, % */
    public const CRIT_DAMAGE_BASE = 175;

    /** Рост множителя критического урона за очко интуиции, % */
    public const CRIT_DAMAGE_PER_INT = 1;

    /**
     * Рост критурона от интуиции, нормированный по уровню — «типичный крит»
     * любого уровня получает одинаковую прибавку (жёсткий потолок роста
     * дополнительно страхует софткап в HitCalculator).
     */
    public static function critDamageBonus(float $intuition, int $level): float
    {
        $levelScale = max(1.0, $level / 12);

        return max(0, ($intuition - 1) * self::CRIT_DAMAGE_PER_INT) / $levelScale;
    }

    public function __construct(private readonly PlayerStatService $statService) {}

    public function handle(PlayerChangeStat $event): void
    {
        $player = $event->player;

        $player->hp_max = self::DEFAULT_HP
            + (self::HP_PER_LEVEL * (max(1, (int) $player->lvl) - 1))
            + (self::HP_PER_ENDURANCE * max(0, $player->getEndurance() - 1));
        $player->mp_max = self::DEFAULT_MP + (self::MP_PER_MUD * ($player->getMud() - 1));

        $player->save();

        // Set hp_now/mp_now to full bonus-aware maximum
        $sheet = $this->statService->resolve($player);
        $player->hp_now = $sheet->getHpMax();
        $player->mp_now = $sheet->getMpMax();
        $player->save();
    }
}
