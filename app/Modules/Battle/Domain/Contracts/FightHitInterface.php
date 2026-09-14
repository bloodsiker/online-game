<?php

declare(strict_types=1);

namespace App\Modules\Battle\Domain\Contracts;

use App\Modules\Battle\Domain\Enums\CombatClass;

interface FightHitInterface
{
    public function getCritical(): int;

    public function getDodge(): int;

    /** Итоговый интеллект (с учётом экипировки) — масштабирует урон атакующих заклинаний, см. MagicAttackStrategy */
    public function getIntelligence(): int;

    /** Магическое сопротивление (Мудрость + экипировка) — единственная защита от магии, см. MagicHitCalculator */
    public function getMagicResistance(): int;

    /** Флэт-бонус к силе заклинаний ИСКЛЮЧИТЕЛЬНО с экипировки (посох/фолиант и т.п.) — интеллект считается отдельно */
    public function getMagicAttack(): int;

    /** Шанс крита заклинания, % — база 0, даёт только экипировка (фолиант). */
    public function getMagicCriticalChance(): int;

    public function getArmor(): int;

    public function getCombatClass(): CombatClass;

    /**
     * Доля выраженности класса у бойца (0..1): насколько билд «танковый»,
     * «уворотливый» или «критовый». Контры треугольника масштабируются
     * непрерывно от долей, а не от дискретного класса — гибрид получает
     * пропорционально ослабленные бонусы и штрафы.
     */
    public function getClassShare(CombatClass $class): float;

    /** Множитель физического критического урона в процентах (175 = ×1.75) */
    public function getCritDamage(): int;

    /**
     * Множитель магического критического урона в процентах — база 175,
     * растёт исключительно с экипировки (см. ShareItemStatType::MAGIC_CRIT_DAMAGE),
     * не зависит от Интуиции.
     */
    public function getMagicCritDamage(): int;

    /**
     * Уровень бойца — масштабирует константы боя (K шансов, знаменатель брони),
     * чтобы баланс не разваливался с ростом стат.
     */
    public function getLevel(): int;

    /**
     * Блок щитом (защитника): шанс срабатывания, %; при срабатывании часть
     * входящего урона (flat + percent от урона) полностью гасится И
     * отражается атакующему — см. HitCalculator::applyShieldBlock.
     */
    public function getBlockChance(): int;

    public function getBlockFlat(): int;

    public function getBlockPercent(): int;
}
