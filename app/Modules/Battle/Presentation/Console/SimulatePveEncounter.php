<?php

declare(strict_types=1);

namespace App\Modules\Battle\Presentation\Console;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Domain\Services\PlayerStatFormulas;
use Illuminate\Console\Command;

/**
 * PvE-проверка конкретного монстра (статы задаёт админ вручную) против типовых
 * билдов игрока того же уровня: показывает винрейт и среднее число раундов —
 * помогает понять, ощущается ли монстр как «танк»/«уворотливый»/«крит-машина»
 * так, как задумано, и не является ли он слишком лёгким/непробиваемым.
 *
 * Статы напрямую (для нового/непроверенного монстра):
 *   php artisan battle:simulate-pve --level=50 --hp=600 --min-dmg=40 --max-dmg=60 --armor=80
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
        {--dodge=0 : Уворот монстра}
        {--critical=0 : Крит монстра}
        {--player-level= : Уровень игрока, если отличается от уровня монстра}
        {--fights=2000}
        {--max-rounds=200 : Потолок раундов на бой — если бой не завершился, засчитывается как «ничья»}';

    protected $description = 'PvE: типовые билды игрока против монстра с заданными (или взятыми из базы) статами';

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
                'dodge' => (int) $this->option('dodge'),
                'critical' => (int) $this->option('critical'),
            ]);

        $playerLevel = (int) ($this->option('player-level') ?: $monster->getLevel());
        $fights = (int) $this->option('fights');
        $maxRounds = (int) $this->option('max-rounds');
        $monsterHp = (int) $monster->hp;

        $this->info(sprintf(
            '%s: lvl %d, класс %s (доля %.0f%%), HP %d, урон %d–%d, броня %d, уворот %d, крит %d | игрок: lvl %d',
            $this->option('id') ? $monster->name : 'Монстр',
            $monster->getLevel(),
            $monster->getCombatClass()->getLabel(),
            100 * $monster->getClassShare($monster->getCombatClass()),
            $monsterHp,
            $monster->min_dmg,
            $monster->max_dmg,
            $monster->getArmor(),
            $monster->getDodge(),
            $monster->getCritical(),
            $playerLevel,
        ));

        $budget = max(8, 8 * ($playerLevel - 1));
        $players = $this->playerBuilds($budget, $playerLevel);

        $rows = [];
        foreach ($players as $name => $player) {
            $wins = 0;
            $draws = 0;
            $roundsToWin = [];
            $roundsToLose = [];

            for ($i = 0; $i < $fights; $i++) {
                $outcome = $this->fight($calc, $magicCalc, $player, $monster, $monsterHp, $maxRounds);

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

        $makeMage = fn (float $int, float $wis, float $end) => new MageFighter(
            intelligence: (int) round($budget * $int),
            wisdom: (int) round($budget * $wis),
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
            // компенсируется выносливостью и уроном заклинаний (см. spellFor()).
            'Маг' => $makeMage(0.5, 0.2, 0.3),
        ];
    }

    private function fight(HitCalculator $calc, MagicHitCalculator $magicCalc, SimFighter $player, Monster $monster, int $monsterMaxHp, int $maxRounds): PveOutcome
    {
        $playerHp = $player->realHp();
        $monsterHpLeft = $monsterMaxHp;
        $spell = $player instanceof MageFighter ? $this->spellFor($player->level) : null;

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
     * Один из 3 стартовых атакующих спеллов (database/seeders/AttackSkillSeeder.php) —
     * берётся тот, чей уровень открытия ближе всего игроку снизу: fire_spark (lvl 1),
     * flame_barrage (lvl 20), incinerating_vortex (lvl 55). Значения захардкожены, а не
     * читаются из БД — симулятор остаётся чистым in-memory расчётом, как и остальные билды;
     * держать в синхроне с AttackSkillSeeder / MagicBookStarterSeeder (power_coefficient) и
     * реальной БД game.magic_skills при тюнинге.
     *
     * @return array{name: string, min: int, max: int, power: float}
     */
    private function spellFor(int $level): array
    {
        return match (true) {
            $level >= 55 => ['name' => 'Испепеляющий вихрь', 'min' => 30, 'max' => 44, 'power' => 0.15],
            $level >= 20 => ['name' => 'Огненный залп', 'min' => 12, 'max' => 18, 'power' => 0.15],
            default => ['name' => 'Огненная искра', 'min' => 4, 'max' => 7, 'power' => 0.15],
        };
    }
}

/**
 * Синтетический маг: как SimFighter, но сила/ловкость/интуиция не качаются вовсе —
 * бюджет весь в интеллекте (урон заклинаний, см. MagicHitCalculator::magicPower)
 * и мудрости (магический резист); броня/уворот/крит от мили-стат — всегда 0.
 * Урон по монстру считается не через minDmg()/maxDmg(), а через spellFor() + MagicHitCalculator.
 */
final class MageFighter extends SimFighter
{
    public function __construct(
        public int $intelligence,
        public int $wisdom,
        int $endurance = 0,
        int $level = 12,
    ) {
        parent::__construct(strength: 0, agility: 0, intuition: 0, endurance: $endurance, level: $level);
    }

    public function getIntelligence(): int
    {
        return $this->intelligence;
    }

    public function getMagicResistance(): int
    {
        return max(0, ($this->wisdom - 1) * PlayerStatFormulas::MAGIC_RESIST_PER_WIS);
    }
}

final readonly class PveOutcome
{
    public function __construct(
        public string $result,
        public int $rounds,
    ) {}
}
