<?php

declare(strict_types=1);

namespace App\Modules\Battle\Presentation\Console;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
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

    public function handle(HitCalculator $calc): int
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
                $outcome = $this->fight($calc, $player, $monster, $monsterHp, $maxRounds);

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

        // Сила есть у всех (нужна под требования вещей), выносливость — типовой запас живучести
        return [
            'Танк' => $make(0.55, 0.1, 0.1, 0.25),
            'Уворот' => $make(0.2, 0.5, 0.1, 0.2),
            'Крит' => $make(0.2, 0.1, 0.5, 0.2),
            'Универсал' => $make(0.28, 0.28, 0.24, 0.2),
        ];
    }

    private function fight(HitCalculator $calc, SimFighter $player, Monster $monster, int $monsterMaxHp, int $maxRounds): PveOutcome
    {
        $playerHp = $player->realHp();
        $monsterHpLeft = $monsterMaxHp;

        for ($round = 1; $round <= $maxRounds; $round++) {
            // Игрок всегда атакует первым — как в реальном бою (AttackService → MonsterAttackService)
            $hit = $calc->hit($player, $monster, $player->minDmg(), $player->maxDmg());
            if (! $hit->isDodge()) {
                $monsterHpLeft -= $hit->getDamage();
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
}

final readonly class PveOutcome
{
    public function __construct(
        public string $result,
        public int $rounds,
    ) {}
}
