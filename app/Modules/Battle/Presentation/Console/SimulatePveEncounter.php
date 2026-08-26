<?php

declare(strict_types=1);

namespace App\Modules\Battle\Presentation\Console;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Domain\Services\ExperienceCurve;
use App\Modules\Player\Domain\Services\PlayerStatFormulas;
use Illuminate\Console\Command;

/**
 * PvE-проверка конкретного монстра (статы задаёт админ вручную) против типовых
 * билдов игрока того же уровня: показывает винрейт и среднее число раундов —
 * помогает понять, ощущается ли монстр как «танк»/«уворотливый»/«крит-машина»
 * так, как задумано, и не является ли он слишком лёгким/непробиваемым.
 *
 * Статы напрямую (для нового/непроверенного монстра):
 *   php artisan battle:simulate-pve --level=50 --hp=600 --min-dmg=40 --max-dmg=60 --armor=80 --magic-resistance=100
 *
 * Или по ID уже созданного в админке монстра:
 *   php artisan battle:simulate-pve --id=12
 */
class SimulatePveEncounter extends Command
{
    protected $signature = 'battle:simulate-pve
        {--id= : ID монстра из базы — статы берутся оттуда, остальные опции игнорируются}
        {--level=1 : Уровень монстра (и игрока, если не задан --player-level)}
        {--hp=100 : HP монстра}
        {--min-dmg=5 : Минимальный урон монстра}
        {--max-dmg=10 : Максимальный урон монстра}
        {--armor=0 : Броня монстра}
        {--magic-resistance=0 : Магическое сопротивление монстра}
        {--dodge=0 : Уворот монстра}
        {--critical=0 : Крит монстра}
        {--player-level= : Уровень игрока, если отличается от уровня монстра}
        {--fights=2000}
        {--max-rounds=200 : Потолок раундов на бой — если бой не завершился, засчитывается как «ничья»}';

    protected $description = 'PvE: типовые билды игрока против монстра с заданными (или взятыми из базы) статами';

    /**
     * Атакующие спеллы (AttackSkillSeeder), от старшего к младшему — берётся
     * первый, чьи требования (magic_skill_requirements) выполнены. Гейт НЕ по
     * уровню персонажа — по уровню навыка «Колдовство» + intelligence/wisdom
     * (см. MagicSkillRequirementService::check, AND по всем требованиям).
     *
     * @var array<int, array{name: string, min: int, max: int, power: float, skill: int, intelligence: int, wisdom: int}>
     */
    private const SPELLS = [
        ['name' => 'Испепеляющий вихрь', 'min' => 30, 'max' => 44, 'power' => 0.15, 'skill' => 55, 'intelligence' => 90, 'wisdom' => 45],
        ['name' => 'Огненный залп', 'min' => 12, 'max' => 18, 'power' => 0.15, 'skill' => 20, 'intelligence' => 35, 'wisdom' => 20],
        ['name' => 'Огненная искра', 'min' => 4, 'max' => 7, 'power' => 0.15, 'skill' => 1, 'intelligence' => 3, 'wisdom' => 2],
    ];

    /** Мирит SkillLevelRequirementSeeder::BASE_EXP — 1 опыт «Колдовства» за успешный каст (weapon=null → expPerHit=1 в PlayerSkillService). */
    private const SKILL_EXP_BASE = 18;

    /** Число прогонов «карьеры» мага для усреднения уровня навыка «Колдовство» на целевом уровне (сглаживает случайность раундов/боя). */
    private const CAREER_TRIALS = 20;

    public function handle(HitCalculator $calc, MagicHitCalculator $magicCalc): int
    {
        $monster = $this->option('id')
            ? Monster::findOrFail((int) $this->option('id'))
            : new Monster([
                'lvl' => (int) $this->option('level'),
                'hp' => (int) $this->option('hp'),
                'min_dmg' => (int) $this->option('min-dmg'),
                'max_dmg' => (int) $this->option('max-dmg'),
                'armor' => (int) $this->option('armor'),
                'magic_resistance' => (int) $this->option('magic-resistance'),
                'dodge' => (int) $this->option('dodge'),
                'critical' => (int) $this->option('critical'),
            ]);

        $playerLevel = (int) ($this->option('player-level') ?: $monster->getLevel());
        $fights = (int) $this->option('fights');
        $maxRounds = (int) $this->option('max-rounds');
        $monsterHp = (int) $monster->hp;

        $this->info(sprintf(
            '%s: lvl %d, класс %s (доля %.0f%%), HP %d, урон %d–%d, броня %d, маг. сопротивление %d, уворот %d, крит %d | игрок: lvl %d',
            $this->option('id') ? $monster->name : 'Монстр',
            $monster->getLevel(),
            $monster->getCombatClass()->getLabel(),
            100 * $monster->getClassShare($monster->getCombatClass()),
            $monsterHp,
            $monster->min_dmg,
            $monster->max_dmg,
            $monster->getArmor(),
            $monster->getMagicResistance(),
            $monster->getDodge(),
            $monster->getCritical(),
            $playerLevel,
        ));

        $budget = max(8, 8 * ($playerLevel - 1));
        $players = $this->playerBuilds($budget, $playerLevel);

        // Спелл мага зависит от «карьеры» (сколько кастов он успел накопить, поднимая
        // навык «Колдовство»), а не от текущего боя — считаем один раз на билд.
        $spells = [];
        foreach ($players as $name => $player) {
            if ($player instanceof MageFighter) {
                $skillLevel = $this->careerSpellcastingLevel($playerLevel, $calc, $magicCalc, $this->isTierOne($name));
                $spell = $this->bestSpellFor($player, $skillLevel);
                $spells[$name] = $spell;

                $this->info(sprintf('  %s: навык «Колдовство» ≈ %d lvl → спелл «%s»', $name, $skillLevel, $spell['name']));
            }
        }

        $rows = [];
        foreach ($players as $name => $player) {
            $spell = $spells[$name] ?? null;
            $wins = 0;
            $draws = 0;
            $roundsToWin = [];
            $roundsToLose = [];

            for ($i = 0; $i < $fights; $i++) {
                $outcome = $this->fight($calc, $magicCalc, $player, $monster, $monsterHp, $maxRounds, $spell);

                if ($outcome->result === 'win') {
                    $wins++;
                    $roundsToWin[] = $outcome->rounds;
                } elseif ($outcome->result === 'draw') {
                    $draws++;
                } else {
                    $roundsToLose[] = $outcome->rounds;
                }
            }

            $rows[] = [
                $name,
                sprintf('%.1f%%', 100 * $wins / $fights),
                $draws > 0 ? sprintf('%.1f%%', 100 * $draws / $fights) : '—',
                $roundsToWin ? sprintf('%.1f', array_sum($roundsToWin) / count($roundsToWin)) : '—',
                $roundsToLose ? sprintf('%.1f', array_sum($roundsToLose) / count($roundsToLose)) : '—',
            ];
        }

        $this->table(['Билд игрока', 'Победа', 'Ничья (таймаут)', 'Раундов до победы', 'Раундов до смерти'], $rows);

        return self::SUCCESS;
    }

    private function isTierOne(string $buildName): bool
    {
        return str_contains($buildName, 'T1');
    }

    /** @return array<string, SimFighter> */
    private function playerBuilds(int $budget, int $level): array
    {
        $make = fn (float $str, float $agil, float $int, float $end) => new SimFighter(
            strength: (int) round($budget * $str),
            agility: (int) round($budget * $agil),
            intuition: (int) round($budget * $int),
            endurance: (int) round($budget * $end),
            level: $level,
        );

        // Сила есть у всех (нужна под требования вещей), выносливость — типовой запас живучести
        return [
            'Танк' => $make(0.55, 0.1, 0.1, 0.25),
            'Уворот' => $make(0.2, 0.5, 0.1, 0.2),
            'Крит' => $make(0.2, 0.1, 0.5, 0.2),
            'Универсал' => $make(0.28, 0.28, 0.24, 0.2),
            // Маг: бюджет идёт в интеллект (сила заклинаний) и мудрость (резист/мана) вместо
            // силы/ловкости/интуиции — от мили-статов у мага броня/уворот/крит всегда нулевые,
            // компенсируется выносливостью и уроном заклинаний (см. bestSpellFor()).
            'Маг' => $this->makeMage($budget, $level, withTierOneSet: false),
            'Маг (T1 сет)' => $this->makeMage($budget, $level, withTierOneSet: true),
        ];
    }

    private function makeMage(int $budget, int $level, bool $withTierOneSet): MageFighter
    {
        $set = $withTierOneSet ? $this->mageTierOneBonuses($level) : ['intelligence' => 0, 'wisdom' => 0, 'endurance' => 0, 'armor' => 0, 'magic_resistance' => 0];

        return new MageFighter(
            intelligence: (int) round($budget * 0.5) + $set['intelligence'],
            wisdom: (int) round($budget * 0.2) + $set['wisdom'],
            endurance: (int) round($budget * 0.3) + $set['endurance'],
            level: $level,
            armorBonus: $set['armor'],
            magicResistanceBonus: $set['magic_resistance'],
        );
    }

    /** @return array{intelligence: int, wisdom: int, endurance: int, armor: int, magic_resistance: int} */
    private function mageTierOneBonuses(int $level): array
    {
        $total = ['intelligence' => 0, 'wisdom' => 0, 'endurance' => 0, 'armor' => 0, 'magic_resistance' => 0];

        $items = [
            1 => ['armor' => 2, 'intelligence' => 1, 'endurance' => 1],
            2 => ['armor' => 1, 'wisdom' => 1],
            4 => ['armor' => 2, 'intelligence' => 1],
            7 => ['armor' => 3, 'wisdom' => 1],
            10 => ['armor' => 4, 'intelligence' => 1, 'endurance' => 1],
            13 => ['armor' => 4, 'wisdom' => 1],
            20 => ['armor' => 5, 'intelligence' => 1, 'wisdom' => 1, 'endurance' => 1, 'magic_resistance' => 2],
        ];

        foreach ($items as $unlockLevel => $bonuses) {
            if ($level < $unlockLevel) {
                continue;
            }

            foreach ($bonuses as $stat => $value) {
                $total[$stat] += $value;
            }
        }

        return $total;
    }

    private function fight(HitCalculator $calc, MagicHitCalculator $magicCalc, SimFighter $player, Monster $monster, int $monsterMaxHp, int $maxRounds, ?array $spell): PveOutcome
    {
        $playerHp = $player->realHp();
        $monsterHpLeft = $monsterMaxHp;

        for ($round = 1; $round <= $maxRounds; $round++) {
            // Игрок всегда атакует первым — как в реальном бою (AttackService → MonsterAttackService)
            if ($spell !== null) {
                // Магия не уворачивается, не критует и не блокируется — см. MagicHitCalculator
                $hit = $magicCalc->hit($player, $monster, $spell['min'], $spell['max'], $spell['power']);
                $monsterHpLeft -= $hit->getDamage();
            } else {
                $hit = $calc->hit($player, $monster, $player->minDmg(), $player->maxDmg());
                if (! $hit->isDodge()) {
                    $monsterHpLeft -= $hit->getDamage();
                }
            }
            if ($monsterHpLeft <= 0) {
                return new PveOutcome('win', $round);
            }

            $counter = $calc->hit($monster, $player, (int) $monster->min_dmg, (int) $monster->max_dmg);
            if (! $counter->isDodge()) {
                $playerHp -= $counter->getDamage();
            }
            if ($playerHp <= 0) {
                return new PveOutcome('lose', $round);
            }
        }

        return new PveOutcome('draw', $maxRounds);
    }

    /**
     * Первый спелл (от старшего к младшему в self::SPELLS), чьи требования —
     * навык «Колдовство» ≥ N и intelligence/wisdom ≥ N — уже выполнены.
     * Огненная искра (skill 1 / int 3 / wis 2) всегда достижима с самого
     * старта (минимальный budget=8 уже даёт int≈4, wis≈2), поэтому массив
     * гарантированно возвращает хотя бы её.
     *
     * @return array{name: string, min: int, max: int, power: float, skill: int, intelligence: int, wisdom: int}
     */
    private function bestSpellFor(MageFighter $mage, int $spellcastingSkillLevel): array
    {
        foreach (self::SPELLS as $spell) {
            if ($spellcastingSkillLevel >= $spell['skill']
                && $mage->getIntelligence() >= $spell['intelligence']
                && $mage->wisdom >= $spell['wisdom']) {
                return $spell;
            }
        }

        return self::SPELLS[array_key_last(self::SPELLS)];
    }

    /**
     * Оценивает уровень навыка «Колдовство» игрока к моменту, когда он
     * достигает $targetLevel — реальный гейт на изучение спеллов теперь идёт
     * по этому навыку, не по уровню персонажа (MagicSkillRequirementService).
     * Навык растёт на 1 опыт за каждый успешный каст (weapon=null у магии →
     * expPerHit=1 в PlayerSkillService::gainExperienceSkill), кривая уровней —
     * SkillLevelRequirementSeeder (exp_diff(L)=18×L, кумулятивно cum(L)=9×L×(L+1)).
     *
     * «Карьера» прогоняется level-by-level: на каждом уровне число нужных ПОБЕД
     * берётся из ExperienceCurve::killsPerLevel против типового моба уровня
     * (typicalMonsterForLevel — приближение к среднему по реальному тир1/тир2
     * ростеру, не точная копия); проигранные попытки тоже тратят раунды (касты),
     * как в реальной игре. Усредняется по self::CAREER_TRIALS прогонам, чтобы
     * сгладить случайность числа раундов на бой.
     */
    private function careerSpellcastingLevel(int $targetLevel, HitCalculator $calc, MagicHitCalculator $magicCalc, bool $withTierOneSet): int
    {
        if ($targetLevel <= 1) {
            return 1;
        }

        $totalSkillLevel = 0;

        for ($trial = 0; $trial < self::CAREER_TRIALS; $trial++) {
            $casts = 0;
            $skillLevel = 1;

            for ($level = 1; $level < $targetLevel; $level++) {
                $levelBudget = max(8, 8 * ($level - 1));
                $mage = $this->makeMage($levelBudget, $level, $withTierOneSet);
                $monster = $this->typicalMonsterForLevel($level);
                $winsNeeded = ExperienceCurve::killsPerLevel($level);

                $wins = 0;
                $attempts = 0;
                $maxAttempts = $winsNeeded * 20;

                while ($wins < $winsNeeded && $attempts < $maxAttempts) {
                    $attempts++;
                    $spell = $this->bestSpellFor($mage, $skillLevel);
                    $outcome = $this->fight($calc, $magicCalc, $mage, $monster, (int) $monster->hp, 200, $spell);

                    $casts += $outcome->rounds;
                    $skillLevel = $this->skillLevelFromCasts($casts);

                    if ($outcome->result === 'win') {
                        $wins++;
                    }
                }
            }

            $totalSkillLevel += $skillLevel;
        }

        return (int) round($totalSkillLevel / self::CAREER_TRIALS);
    }

    /** cum(L) = SKILL_EXP_BASE × L × (L+1) ≤ casts — решение квадратного уравнения относительно L, дискриминант точный квадрат по построению. */
    private function skillLevelFromCasts(int $casts): int
    {
        if ($casts < self::SKILL_EXP_BASE) {
            return 1;
        }

        $level = (int) floor((-self::SKILL_EXP_BASE + sqrt(self::SKILL_EXP_BASE ** 2 + 4 * self::SKILL_EXP_BASE * $casts)) / (2 * self::SKILL_EXP_BASE));

        return max(1, $level);
    }

    /**
     * Условный «типовой» моб уровня для оценки темпа прокачки «Колдовства» —
     * не копия конкретного ростера (RecalibrateLeveling::MONSTERS), а усреднённые
     * по нему параметры (броня/уворот/крит/урон около средних значений тир1-тир2
     * линейки), посчитанные той же формулой, что и реальные мобы.
     */
    private function typicalMonsterForLevel(int $level): Monster
    {
        $hp = MonsterStatFormulas::hp($level, 1.4);
        $armor = MonsterStatFormulas::armorForMitigation($level, 0.10);
        $dodge = MonsterStatFormulas::rawStatForChance($level, 8.0);
        $critical = MonsterStatFormulas::rawStatForChance($level, 8.0);
        [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($level, 11.0);

        return new Monster([
            'lvl' => $level,
            'hp' => $hp,
            'min_dmg' => $minDmg,
            'max_dmg' => $maxDmg,
            'armor' => $armor,
            'dodge' => $dodge,
            'critical' => $critical,
            'magic_resistance' => $level,
        ]);
    }
}

/**
 * Синтетический маг: как SimFighter, но сила/ловкость/интуиция не качаются вовсе —
 * бюджет весь в интеллекте (урон заклинаний, см. MagicHitCalculator::magicPower)
 * и мудрости (магический резист); броня/уворот/крит от мили-стат — всегда 0.
 * Урон по монстру считается не через minDmg()/maxDmg(), а через bestSpellFor() + MagicHitCalculator.
 */
final class MageFighter extends SimFighter
{
    public function __construct(
        public int $intelligence,
        public int $wisdom,
        int $endurance = 0,
        int $level = 12,
        private int $armorBonus = 0,
        private int $magicResistanceBonus = 0,
    ) {
        parent::__construct(strength: 0, agility: 0, intuition: 0, endurance: $endurance, level: $level);
    }

    public function getIntelligence(): int
    {
        return $this->intelligence;
    }

    public function getMagicResistance(): int
    {
        return max(0, ($this->wisdom - 1) * PlayerStatFormulas::MAGIC_RESIST_PER_WIS) + $this->magicResistanceBonus;
    }

    public function getArmor(): int
    {
        return $this->armorBonus;
    }
}

final readonly class PveOutcome
{
    public function __construct(
        public string $result,
        public int $rounds,
    ) {}
}
