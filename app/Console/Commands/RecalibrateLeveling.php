<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Experience;
use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Services\ExperienceCurve;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Применяет пересчитанную кривую опыта и характеристики стартовых мобов
 * к уже существующей БД (в отличие от GenerateSeed, которая только для
 * чистой установки). Безопасно перезаписывает таблицу experiences,
 * обновляет/создаёт 4 стартовых моба по имени и сбрасывает прогресс-бар
 * игроков внутри их текущего уровня под новую кривую (уровень не трогается).
 */
class RecalibrateLeveling extends Command
{
    protected $signature = 'game:recalibrate-leveling {--max-level=100}';

    protected $description = 'Пересчитать таблицу опыта и стартовых мобов (Мышь/Летучая мышь/Волк/Медведь) по формулам';

    /**
     * @var array<string, array{level: int, hpMultiplier: float, armorMitigation: float, dodgePercent: float, critPercent: float, dmgPercent: float, expMultiplier: float, aggression: int}>
     */
    private const MONSTERS = [
        'Мышь' => ['level' => 1, 'hpMultiplier' => 1.0, 'armorMitigation' => 0.00, 'dodgePercent' => 5, 'critPercent' => 5, 'dmgPercent' => 8, 'expMultiplier' => 1.0, 'aggression' => 50],
        'Летучая мышь' => ['level' => 2, 'hpMultiplier' => 1.3, 'armorMitigation' => 0.05, 'dodgePercent' => 8, 'critPercent' => 5, 'dmgPercent' => 10, 'expMultiplier' => 1.1, 'aggression' => 70],
        'Волк' => ['level' => 4, 'hpMultiplier' => 1.8, 'armorMitigation' => 0.12, 'dodgePercent' => 14, 'critPercent' => 16, 'dmgPercent' => 13, 'expMultiplier' => 1.3, 'aggression' => 85],
        'Медведь' => ['level' => 7, 'hpMultiplier' => 1.7, 'armorMitigation' => 0.21, 'dodgePercent' => 7, 'critPercent' => 10, 'dmgPercent' => 13, 'expMultiplier' => 1.6, 'aggression' => 60],
        'Кабан' => ['level' => 10, 'hpMultiplier' => 1.65, 'armorMitigation' => 0.127, 'dodgePercent' => 6, 'critPercent' => 6, 'dmgPercent' => 11, 'expMultiplier' => 1.4, 'aggression' => 90],
        'Разбойник' => ['level' => 13, 'hpMultiplier' => 1.32, 'armorMitigation' => 0.074, 'dodgePercent' => 14, 'critPercent' => 16, 'dmgPercent' => 11, 'expMultiplier' => 1.3, 'aggression' => 75],
        'Тролль' => ['level' => 16, 'hpMultiplier' => 1.73, 'armorMitigation' => 0.175, 'dodgePercent' => 5, 'critPercent' => 5, 'dmgPercent' => 11, 'expMultiplier' => 1.7, 'aggression' => 70],
        'Огр' => ['level' => 20, 'hpMultiplier' => 1.54, 'armorMitigation' => 0.145, 'dodgePercent' => 5, 'critPercent' => 6, 'dmgPercent' => 12.5, 'expMultiplier' => 1.9, 'aggression' => 65],
    ];

    public function handle(): int
    {
        $maxLevel = (int) $this->option('max-level');

        $this->recalculateExperienceTable($maxLevel);
        $this->recalibrateMonsters();
        $this->resyncPlayers();

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function recalculateExperienceTable(int $maxLevel): void
    {
        Experience::query()->delete();

        $rows = [];
        foreach (ExperienceCurve::table($maxLevel) as $lvl => $row) {
            $rows[] = ['lvl' => $lvl, 'exp' => $row['exp'], 'exp_diff' => $row['exp_diff']];
        }
        DB::table('experiences')->insert($rows);

        $this->info("experiences: пересчитано {$maxLevel} уровней.");
    }

    private function recalibrateMonsters(): void
    {
        foreach (self::MONSTERS as $name => $p) {
            [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($p['level'], $p['dmgPercent']);
            $exp = MonsterStatFormulas::expReward($p['level'], $p['expMultiplier']);
            [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp);

            $monster = Monster::firstOrNew(['name' => $name]);
            $monster->lvl = $p['level'];
            $monster->hp = MonsterStatFormulas::hp($p['level'], $p['hpMultiplier']);
            $monster->armor = MonsterStatFormulas::armorForMitigation($p['level'], $p['armorMitigation']);
            $monster->dodge = MonsterStatFormulas::rawStatForChance($p['level'], $p['dodgePercent']);
            $monster->critical = MonsterStatFormulas::rawStatForChance($p['level'], $p['critPercent']);
            $monster->min_dmg = $minDmg;
            $monster->max_dmg = $maxDmg;
            $monster->aggression = $p['aggression'];
            $monster->min_money = $minMoney;
            $monster->max_money = $maxMoney;
            $monster->exp = $exp;
            $monster->is_boss = false;
            $monster->save();

            $this->info("monster «{$name}»: lvl={$monster->lvl} hp={$monster->hp} armor={$monster->armor} dodge={$monster->dodge} crit={$monster->critical} dmg={$monster->min_dmg}-{$monster->max_dmg} exp={$monster->exp} money={$monster->min_money}-{$monster->max_money}");
        }
    }

    private function resyncPlayers(): void
    {
        Player::query()->chunkById(200, function ($players): void {
            foreach ($players as $player) {
                $exp = Experience::where('lvl', $player->lvl)->first();

                if (! $exp) {
                    continue;
                }

                $player->exp = $exp->exp;
                $player->exp_up = $exp->exp + $exp->exp_diff;
                $player->exp_diff = $exp->exp_diff;
                $player->save();
            }
        });

        $this->info('players: прогресс внутри текущего уровня сброшен под новую кривую (уровень не менялся).');
    }
}
