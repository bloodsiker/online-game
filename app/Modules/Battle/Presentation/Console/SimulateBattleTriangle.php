<?php

declare(strict_types=1);

namespace App\Modules\Battle\Presentation\Console;

use App\Listeners\RecalculatePlayerModification;
use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;
use Illuminate\Console\Command;

/**
 * Симуляция баланса боевого треугольника: дуэли билдов с равным бюджетом стат.
 * Выводит матрицу винрейтов — перекосы видно сразу.
 *
 * php artisan battle:simulate --hp-mode=game --levels=12,50,100,200,300,400,500
 */
class SimulateBattleTriangle extends Command
{
    protected $signature = 'battle:simulate
        {--fights=2000}
        {--budget=90}
        {--hp=300}
        {--hp-mode=game : game — реальная формула (уровень+выносливость, 6 билдов с четвёртой статой); level — только уровень, без выносливости; fixed — одинаковый HP (--hp); strength — 10+3×сила (устар.); mixed — 10+10×уровень+1×сила}
        {--realistic : реалистичные билды — классовая стата преобладает, но сила есть у всех (требования вещей); игнорируется при hp-mode=game}
        {--hp-per-end= : override HP за очко выносливости (по умолчанию — игровая константа HP_PER_ENDURANCE)}
        {--levels= : Уровни через запятую (напр. 12,50,100,200,300,400,500); бюджет стат = 8×уровень}';

    protected $description = 'Матрица винрейтов боевого треугольника (танк/уворот/крит и гибриды)';

    public function handle(HitCalculator $calc): int
    {
        $fights = (int) $this->option('fights');
        $hp = (int) $this->option('hp');
        $hpMode = (string) $this->option('hp-mode');

        $levels = $this->option('levels')
            ? array_map('intval', explode(',', (string) $this->option('levels')))
            : [(int) round(((int) $this->option('budget')) / 8) + 1];

        foreach ($levels as $level) {
            $this->runMatrix($calc, $fights, $hp, $hpMode, max(1, $level));
        }

        return self::SUCCESS;
    }

    private function runMatrix(HitCalculator $calc, int $fights, int $hp, string $hpMode, int $level): void
    {
        // ~8 стат за уровень (5 свободных + ~3 расовых)
        $budget = max(8, 8 * ($level - 1));

        $builds = match (true) {
            $hpMode === 'game' => $this->enduranceBuilds($budget, $level),
            (bool) $this->option('realistic') => $this->realisticBuilds($budget, $level),
            default => $this->builds($budget, $level),
        };

        $hpPerEnd = $this->option('hp-per-end') !== null
            ? (float) $this->option('hp-per-end')
            : (float) RecalculatePlayerModification::HP_PER_ENDURANCE;

        $this->info(sprintf(
            'Дуэли: %d боёв на пару, бюджет стат: %d (~%d уровень), билды: %s, HP: %s',
            $fights,
            $budget,
            $level,
            $hpMode === 'game' ? 'танк/уворот/крит + выносливость' : ($this->option('realistic') ? 'реалистичные' : 'чистые'),
            match ($hpMode) {
                'strength' => '10 + 3×сила (устаревшая формула)',
                'level' => sprintf('10 + %d×(уровень−1), без выносливости', RecalculatePlayerModification::HP_PER_LEVEL),
                'mixed' => '10 + 10×(уровень−1) + 1×сила',
                'game' => sprintf('10 + %d×(уровень−1) + %s×выносливость (боевая формула)', RecalculatePlayerModification::HP_PER_LEVEL, $hpPerEnd),
                default => (string) $hp,
            },
        ));

        $names = array_keys($builds);
        $rows = [];

        foreach ($names as $rowName) {
            $row = ['build' => $rowName];
            foreach ($names as $colName) {
                if ($rowName === $colName) {
                    $row[$colName] = '—';

                    continue;
                }
                $wins = 0;
                $hpRow = $this->hpFor($builds[$rowName], $hpMode, $hp, $level);
                $hpCol = $this->hpFor($builds[$colName], $hpMode, $hp, $level);
                for ($i = 0; $i < $fights; $i++) {
                    if ($this->duel($calc, $builds[$rowName], $builds[$colName], $hpRow, $hpCol, firstAttacker: $i % 2 === 0)) {
                        $wins++;
                    }
                }
                $row[$colName] = sprintf('%.1f%%', 100 * $wins / $fights);
            }
            $rows[] = $row;
        }

        $this->table(array_merge(['build ↓ vs →'], $names), array_map('array_values', $rows));
    }

    /** @return array<string, SimFighter> */
    private function builds(int $budget, int $level): array
    {
        $make = fn (float $str, float $agil, float $int) => new SimFighter(
            strength: (int) round($budget * $str),
            agility: (int) round($budget * $agil),
            intuition: (int) round($budget * $int),
            level: $level,
        );

        return [
            'Танк' => $make(0.9, 0.05, 0.05),
            'Уворот' => $make(0.05, 0.9, 0.05),
            'Крит' => $make(0.05, 0.05, 0.9),
            'Танк/Крит' => $make(0.55, 0.05, 0.4),
            'Танк/Уворот' => $make(0.55, 0.4, 0.05),
            'Уворот/Крит' => $make(0.05, 0.55, 0.4),
        ];
    }

    /**
     * Реалистичные билды: классовая стата преобладает, но силу качают все —
     * под требования вещей и ради выживания.
     *
     * @return array<string, SimFighter>
     */
    private function realisticBuilds(int $budget, int $level): array
    {
        $make = fn (float $str, float $agil, float $int) => new SimFighter(
            strength: (int) round($budget * $str),
            agility: (int) round($budget * $agil),
            intuition: (int) round($budget * $int),
            level: $level,
        );

        return [
            'Танк' => $make(0.7, 0.15, 0.15),
            'Уворот' => $make(0.3, 0.6, 0.1),
            'Крит' => $make(0.3, 0.1, 0.6),
            'Универсал' => $make(0.34, 0.33, 0.33),
        ];
    }

    private function hpFor(SimFighter $fighter, string $mode, int $fixed, int $level): int
    {
        $hpPerEnd = $this->option('hp-per-end') !== null
            ? (float) $this->option('hp-per-end')
            : (float) RecalculatePlayerModification::HP_PER_ENDURANCE;

        return match ($mode) {
            'strength' => $fighter->legacyStrengthHp(),
            'level' => RecalculatePlayerModification::DEFAULT_HP + RecalculatePlayerModification::HP_PER_LEVEL * ($level - 1),
            'mixed' => 10 + 10 * ($level - 1) + max(0, $fighter->strength - 1),
            'game' => RecalculatePlayerModification::DEFAULT_HP
                + RecalculatePlayerModification::HP_PER_LEVEL * ($level - 1)
                + (int) round($hpPerEnd * max(0, $fighter->endurance - 1)),
            default => $fixed,
        };
    }

    /**
     * Билды с учётом статы «Выносливость» (только HP): бюджет делится между
     * классовой статой и выносливостью — виден трейдофф «живучесть vs сила класса».
     *
     * @return array<string, SimFighter>
     */
    private function enduranceBuilds(int $budget, int $level): array
    {
        $make = fn (float $str, float $agil, float $int, float $end) => new SimFighter(
            strength: (int) round($budget * $str),
            agility: (int) round($budget * $agil),
            intuition: (int) round($budget * $int),
            endurance: (int) round($budget * $end),
            level: $level,
        );

        return [
            'Танк' => $make(0.5, 0.1, 0.1, 0.3),
            'Уворот' => $make(0.15, 0.45, 0.1, 0.3),
            'Крит' => $make(0.15, 0.1, 0.45, 0.3),
            'Крит-стекло' => $make(0.15, 0.1, 0.65, 0.1),
            'Универсал' => $make(0.25, 0.25, 0.25, 0.25),
            'Жирдяй' => $make(0.15, 0.1, 0.15, 0.6),
        ];
    }

    private function duel(HitCalculator $calc, SimFighter $a, SimFighter $b, int $hpA, int $hpB, bool $firstAttacker): bool
    {
        $turnA = $firstAttacker;

        for ($round = 0; $round < 1000; $round++) {
            if ($turnA) {
                $hit = $calc->hit($a, $b, $a->minDmg(), $a->maxDmg());
                $hpB -= $hit->isDodge() ? 0 : $hit->getDamage();
                if ($hpB <= 0) {
                    return true;
                }
            } else {
                $hit = $calc->hit($b, $a, $b->minDmg(), $b->maxDmg());
                $hpA -= $hit->isDodge() ? 0 : $hit->getDamage();
                if ($hpA <= 0) {
                    return false;
                }
            }
            $turnA = ! $turnA;
        }

        return $hpA >= $hpB;
    }
}

/**
 * Синтетический боец: статы конвертируются по тем же правилам, что у игроков.
 */
class SimFighter implements FightHitInterface
{
    /** Базовый урон «оружия» одинаков у всех билдов */
    private const BASE_MIN_DMG = 10;

    private const BASE_MAX_DMG = 15;

    public function __construct(
        public int $strength,
        public int $agility,
        public int $intuition,
        public int $endurance = 0,
        public int $level = 12,
    ) {}

    public function minDmg(): int
    {
        return (int) floor(self::BASE_MIN_DMG * $this->weaponScale() * $this->strengthMultiplier());
    }

    public function maxDmg(): int
    {
        return (int) floor(self::BASE_MAX_DMG * $this->weaponScale() * $this->strengthMultiplier());
    }

    /** Оружие растёт с уровнем — как в реальной игре у шмота своего тира */
    private function weaponScale(): float
    {
        return max(1.0, $this->level / 12);
    }

    private function strengthMultiplier(): float
    {
        return 1 + RecalculatePlayerModification::strengthDamagePercent((float) $this->strength, $this->level) / 100;
    }

    /** HP по устаревшей формуле (10 + 3×сила) — только для сравнения «как было» */
    public function legacyStrengthHp(): int
    {
        return RecalculatePlayerModification::DEFAULT_HP + 3 * max(0, $this->strength - 1);
    }

    /** HP по актуальной боевой формуле: уровень + выносливость */
    public function realHp(): int
    {
        return RecalculatePlayerModification::DEFAULT_HP
            + RecalculatePlayerModification::HP_PER_LEVEL * max(0, $this->level - 1)
            + RecalculatePlayerModification::HP_PER_ENDURANCE * max(0, $this->endurance - 1);
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getCritical(): int
    {
        return max(0, ($this->intuition - 1) * RecalculatePlayerModification::CRITICAL_PER_INT);
    }

    public function getDodge(): int
    {
        return max(0, ($this->agility - 1) * RecalculatePlayerModification::DODGE_PER_AGILITY);
    }

    public function getArmor(): int
    {
        return max(0, ($this->strength - 1) * RecalculatePlayerModification::ARMOR_PER_STR);
    }

    public function getCombatClass(): CombatClass
    {
        return match (true) {
            $this->strength >= $this->agility && $this->strength >= $this->intuition => CombatClass::TANK,
            $this->agility >= $this->intuition => CombatClass::DODGE,
            default => CombatClass::CRIT,
        };
    }

    public function getClassShare(CombatClass $class): float
    {
        $total = max(1.0, $this->strength + $this->agility + $this->intuition);

        return match ($class) {
            CombatClass::TANK => $this->strength / $total,
            CombatClass::DODGE => $this->agility / $total,
            CombatClass::CRIT => $this->intuition / $total,
        };
    }

    public function getCritDamage(): int
    {
        return (int) round(RecalculatePlayerModification::CRIT_DAMAGE_BASE
            + RecalculatePlayerModification::critDamageBonus((float) $this->intuition, $this->level));
    }

    // Синтетические билды симулятора щит не носят — блок всегда 0
    public function getBlockChance(): int
    {
        return 0;
    }

    public function getBlockFlat(): int
    {
        return 0;
    }

    public function getBlockPercent(): int
    {
        return 0;
    }
}
