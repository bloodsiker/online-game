<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Database\Seeder;

/**
 * Разводит все 8 видов монстров «Сторожевых Холмов» по разным уровням
 * (35-45 вместо единых 38/41) по прямому указанию пользователя — раньше все
 * виды делили одни и те же 2 уровня, что делало зону монотонной.
 *
 * Роль (hpMultiplier/armorMitigation/dodgePercent/critPercent/dmgPercent/
 * aggression) каждого вида не меняется — только уровень, на котором эта роль
 * вычисляется формулами MonsterStatFormulas. Обновляет строки ПО МЕСТУ
 * (находит по текущим name+lvl, пишет новые lvl+статы в тот же id), а не
 * создаёт новые — «Земляной страж»/«Ветряной страж» уже имеют привязанные
 * дропы в monster_has_items, терять их пересозданием нельзя.
 */
class WatchHillsMonsterLevelSpreadSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, currentLvl: int, newLvl: int,
     *     hpMultiplier: float, armorMitigation: float, dodgePercent: float,
     *     critPercent: float, dmgPercent: float, expMultiplier: float, aggression: int,
     * }>
     */
    private const CHANGES = [
        ['name' => 'Земляной страж', 'currentLvl' => 38, 'newLvl' => 35, 'hpMultiplier' => 1.55, 'armorMitigation' => 0.30, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 11.0, 'expMultiplier' => 1.0, 'aggression' => 60],
        ['name' => 'Земляной страж', 'currentLvl' => 41, 'newLvl' => 38, 'hpMultiplier' => 1.60, 'armorMitigation' => 0.33, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 11.3, 'expMultiplier' => 1.0, 'aggression' => 60],

        ['name' => 'Ветряной страж', 'currentLvl' => 38, 'newLvl' => 36, 'hpMultiplier' => 0.85, 'armorMitigation' => 0, 'dodgePercent' => 45, 'critPercent' => 0, 'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 65],
        ['name' => 'Ветряной страж', 'currentLvl' => 41, 'newLvl' => 39, 'hpMultiplier' => 0.88, 'armorMitigation' => 0, 'dodgePercent' => 52, 'critPercent' => 0, 'dmgPercent' => 10.8, 'expMultiplier' => 1.0, 'aggression' => 65],

        ['name' => 'Костяной Часовой', 'currentLvl' => 38, 'newLvl' => 37, 'hpMultiplier' => 0.70, 'armorMitigation' => 0.18, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 10.0, 'expMultiplier' => 1.0, 'aggression' => 60],
        ['name' => 'Костяной Часовой', 'currentLvl' => 41, 'newLvl' => 40, 'hpMultiplier' => 0.73, 'armorMitigation' => 0.20, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 10.3, 'expMultiplier' => 1.0, 'aggression' => 60],

        ['name' => 'Дух Павшего Знаменосца', 'currentLvl' => 38, 'newLvl' => 35, 'hpMultiplier' => 1.20, 'armorMitigation' => 0, 'dodgePercent' => 20, 'critPercent' => 10, 'dmgPercent' => 11.5, 'expMultiplier' => 1.0, 'aggression' => 65],
        ['name' => 'Дух Павшего Знаменосца', 'currentLvl' => 41, 'newLvl' => 39, 'hpMultiplier' => 1.25, 'armorMitigation' => 0, 'dodgePercent' => 23, 'critPercent' => 12, 'dmgPercent' => 11.8, 'expMultiplier' => 1.0, 'aggression' => 65],

        ['name' => 'Стальной Страж', 'currentLvl' => 38, 'newLvl' => 38, 'hpMultiplier' => 2.00, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 50],
        ['name' => 'Стальной Страж', 'currentLvl' => 41, 'newLvl' => 42, 'hpMultiplier' => 2.10, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 10.8, 'expMultiplier' => 1.0, 'aggression' => 50],

        ['name' => 'Пепельный Страж', 'currentLvl' => 38, 'newLvl' => 39, 'hpMultiplier' => 0.75, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 16.0, 'expMultiplier' => 1.0, 'aggression' => 80],
        ['name' => 'Пепельный Страж', 'currentLvl' => 41, 'newLvl' => 43, 'hpMultiplier' => 0.78, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 16.8, 'expMultiplier' => 1.0, 'aggression' => 80],

        ['name' => 'Теневой Страж', 'currentLvl' => 38, 'newLvl' => 40, 'hpMultiplier' => 0.85, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 45, 'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 70],
        ['name' => 'Теневой Страж', 'currentLvl' => 41, 'newLvl' => 44, 'hpMultiplier' => 0.88, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 52, 'dmgPercent' => 10.8, 'expMultiplier' => 1.0, 'aggression' => 70],

        ['name' => 'Туманный Страж', 'currentLvl' => 38, 'newLvl' => 41, 'hpMultiplier' => 0.90, 'armorMitigation' => 0, 'dodgePercent' => 25, 'critPercent' => 20, 'dmgPercent' => 10.8, 'expMultiplier' => 1.0, 'aggression' => 55],
        ['name' => 'Туманный Страж', 'currentLvl' => 41, 'newLvl' => 45, 'hpMultiplier' => 0.93, 'armorMitigation' => 0, 'dodgePercent' => 28, 'critPercent' => 23, 'dmgPercent' => 11.1, 'expMultiplier' => 1.0, 'aggression' => 55],
    ];

    public function run(): void
    {
        $updated = 0;

        foreach (self::CHANGES as $c) {
            $monster = Monster::query()->where('name->ru', $c['name'])->where('lvl', $c['currentLvl'])->first();

            if ($monster === null) {
                $this->command?->warn(sprintf('не найден: «%s» lvl %d', $c['name'], $c['currentLvl']));

                continue;
            }

            [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($c['newLvl'], $c['dmgPercent']);
            $exp = MonsterStatFormulas::expReward($c['newLvl'], $c['expMultiplier']);
            [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $c['newLvl']);

            $monster->lvl = $c['newLvl'];
            $monster->hp = MonsterStatFormulas::hp($c['newLvl'], $c['hpMultiplier']);
            $monster->armor = MonsterStatFormulas::armorForMitigation($c['newLvl'], $c['armorMitigation']);
            $monster->dodge = MonsterStatFormulas::rawStatForChance($c['newLvl'], $c['dodgePercent']);
            $monster->critical = MonsterStatFormulas::rawStatForChance($c['newLvl'], $c['critPercent']);
            $monster->min_dmg = $minDmg;
            $monster->max_dmg = $maxDmg;
            $monster->aggression = $c['aggression'];
            $monster->exp = $exp;
            $monster->min_money = $minMoney;
            $monster->max_money = $maxMoney;
            $monster->save();

            $updated++;

            $this->command?->info(sprintf(
                'monster «%s» %d->%d: hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                $monster->name, $c['currentLvl'], $monster->lvl, $monster->hp, $monster->armor, $monster->dodge,
                $monster->critical, $monster->min_dmg, $monster->max_dmg, $monster->exp,
                $monster->min_money, $monster->max_money,
            ));
        }

        $this->command?->info("WatchHillsMonsterLevelSpreadSeeder: обновлено монстров — {$updated}");
    }
}
