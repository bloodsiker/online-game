<?php

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;

readonly class HitCalculator
{
    /** Базовый шанс уворота/крита при равных статах, % */
    private const BASE_CHANCE = 5.0;

    /** Нижний порог шанса: отставание в статах не обнуляет уворот/крит полностью */
    private const MIN_CHANCE = 5.0;

    /** Максимальная прибавка к базовому шансу от перевеса стат, % (потолок = 5 + 70 = 75%) */
    private const MAX_STAT_BONUS = 70.0;

    /**
     * Уровень, на котором калиброваны константы: K шансов и знаменатель брони
     * масштабируются от среднего уровня бойцов относительно этого якоря,
     * иначе на высоких уровнях шансы прилипают к потолку, а броня растёт бесконечно.
     */
    private const REFERENCE_LEVEL = 12.0;

    /** Константа смягчения шансов на референсном уровне (перевес K даёт половину прибавки) */
    private const SOFTCAP_K = 55.0;

    /** Знаменатель формулы брони на референсном уровне: митигция = A/(A+armor) */
    private const ARMOR_CONSTANT = 220.0;

    /** Софткап критурона: рост выше базы асимптотически ограничен CRIT_DAMAGE_CAP */
    private const CRIT_DAMAGE_BASE = 175.0;

    private const CRIT_DAMAGE_CAP = 300.0;

    /** Контра «Танк > Уворот»: максимальное срезание шанса уворота (при чистом танке) */
    private const DODGE_COUNTER = 0.27;

    /** Контра «Уворот > Крит»: максимальное срезание шанса крита (при чистом увороте) */
    private const CRIT_COUNTER = 0.05;

    /** Контра «Крит > Танк»: максимальное пробитие брони на крите (при чистом крите) */
    private const ARMOR_PIERCE = 0.92;

    public function __construct(
        private RandomizerInterface $random,
    ) {}

    public function hit(FightHitInterface $attacker, FightHitInterface $defender, int $min, int $max): FightHitDTO
    {
        $dto = new FightHitDTO;

        if ($this->isDodge($attacker, $defender)) {
            return $dto->setDodge(true);
        }

        $damage = $this->random->between(min($min, $max), max($min, $max));
        $isCrit = $this->isCritical($attacker, $defender);

        if ($isCrit) {
            $dto->setCritical(true);
            $damage = (int) round($damage * $this->effectiveCritDamage($attacker) / 100);
        }

        $armorConstant = self::ARMOR_CONSTANT * $this->levelScale($attacker, $defender);
        $final = $damage * ($armorConstant / ($armorConstant + $this->effectiveArmor($attacker, $defender, $isCrit)));
        $final = max(1, (int) round($final));

        [$final, $reflected, $blocked] = $this->applyShieldBlock($defender, $final);

        return $dto->setDamage($final)->setReflectedDamage($reflected)->setBlocked($blocked);
    }

    /**
     * Блок щитом защитника: с шансом getBlockChance() часть входящего урона
     * (flat + percent от урона) гасится ПОЛНОСТЬЮ и в том же объёме
     * отражается атакующему — см. StarterEquipmentSeeder / обсуждение баланса щита.
     *
     * @return array{0: int, 1: int, 2: bool} [итоговый урон защитнику, отражённый урон атакующему, сработал ли блок]
     */
    private function applyShieldBlock(FightHitInterface $defender, int $damage): array
    {
        if ($defender->getBlockChance() <= 0 || ! $this->random->chance((float) $defender->getBlockChance())) {
            return [$damage, 0, false];
        }

        // Не даём блоку срезать урон до 0 — хотя бы 1 всегда проходит
        $absorbed = min($damage - 1, $defender->getBlockFlat() + (int) round($damage * $defender->getBlockPercent() / 100));

        return [$damage - $absorbed, $absorbed, true];
    }

    /**
     * Масштаб констант от среднего уровня сторон: на 12 уровне ×1,
     * дальше пропорционально — сохраняет форму кривых на любом уровне.
     */
    private function levelScale(FightHitInterface $attacker, FightHitInterface $defender): float
    {
        $avgLevel = ($attacker->getLevel() + $defender->getLevel()) / 2;

        return max(1.0, $avgLevel / self::REFERENCE_LEVEL);
    }

    /**
     * Выраженность класса сверх «универсальной» трети: у ровного билда контры
     * нулевые, у чистого (~0.9 доли) — почти максимальные. Без вычитания базы
     * универсал получал бы понемногу от всех трёх контр сразу.
     */
    private function classShareAboveBaseline(FightHitInterface $fighter, CombatClass $class): float
    {
        return max(0.0, ($fighter->getClassShare($class) - 1 / 3) / (2 / 3));
    }

    /**
     * Крит > Танк: критовость атакующего пробивает броню на крите.
     */
    private function effectiveArmor(FightHitInterface $attacker, FightHitInterface $defender, bool $isCrit): float
    {
        $armor = (float) $defender->getArmor();

        if (! $isCrit) {
            return $armor;
        }

        $pierce = self::ARMOR_PIERCE * $this->classShareAboveBaseline($attacker, CombatClass::TANK->beatenBy());

        return $armor * (1 - $pierce);
    }

    /** Критурон с мягким потолком: 175% → асимптотически к 300% */
    private function effectiveCritDamage(FightHitInterface $attacker): float
    {
        $raw = (float) $attacker->getCritDamage();

        if ($raw <= self::CRIT_DAMAGE_BASE) {
            return $raw;
        }

        $growth = $raw - self::CRIT_DAMAGE_BASE;
        $span = self::CRIT_DAMAGE_CAP - self::CRIT_DAMAGE_BASE;

        return self::CRIT_DAMAGE_BASE + $span * $growth / ($growth + $span);
    }

    /**
     * Танк > Уворот: танковость атакующего продавливает уворот защитника.
     */
    private function isDodge(FightHitInterface $attacker, FightHitInterface $defender): bool
    {
        $chance = $this->statChance($defender->getDodge(), $attacker->getDodge(), $this->levelScale($attacker, $defender));

        $reduction = self::DODGE_COUNTER * $this->classShareAboveBaseline($attacker, CombatClass::DODGE->beatenBy());

        return $this->random->chance($chance * (1 - $reduction));
    }

    /**
     * Уворот > Крит: уворотливость защитника сбивает прицел атакующего.
     */
    private function isCritical(FightHitInterface $attacker, FightHitInterface $defender): bool
    {
        $chance = $this->statChance($attacker->getCritical(), $defender->getCritical(), $this->levelScale($attacker, $defender));

        $reduction = self::CRIT_COUNTER * $this->classShareAboveBaseline($defender, CombatClass::CRIT->beatenBy());

        return $this->random->chance($chance * (1 - $reduction));
    }

    /**
     * Шанс от перевеса стат с убывающей отдачей и мягким потолком:
     * равные статы → 5%; перевес K(уровень) → 40%; асимптотически к 75%.
     * Отставание плавно уводит шанс к нижнему порогу 5%, но не в ноль.
     */
    private function statChance(int $attackStat, int $counterStat, float $levelScale): float
    {
        $delta = (float) ($attackStat - $counterStat);
        $k = self::SOFTCAP_K * $levelScale;
        $bonus = self::MAX_STAT_BONUS * $delta / (abs($delta) + $k);

        return max(self::MIN_CHANCE, self::BASE_CHANCE + $bonus);
    }
}
