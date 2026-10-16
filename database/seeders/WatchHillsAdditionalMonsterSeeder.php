<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;

/**
 * Дополнительные виды монстров карты «Сторожевые Холмы» (локации 1116-1278),
 * восстановленные по памяти пользователя после аварии БД 25 сентября 2026:
 * «Теневой Страж», «Стальной Страж», «Пепельный Страж», «Туманный Страж»,
 * «Дух Павшего Знаменосца», «Костяной Часовой» — оригиналы не попали ни в
 * один сохранившийся binlog, стат-лайны воссозданы заново по формулам
 * MonsterStatFormulas, откалиброванным на уже восстановленных
 * «Земляной страж»/«Ветряной страж» тех же уровней (см. WatchHillsMonsterSeeder).
 *
 * Только СОЗДАНИЕ мобов — расстановка в location_has_monsters отдельным шагом.
 *
 * Роли (Земляной=броня, Ветряной=уворот уже заняты):
 *   Теневой Страж          — чистый крит (зеркало Ветряного, но по крит-статy)
 *   Стальной Страж         — чистая живучесть без брони (сырой hp-танк)
 *   Пепельный Страж        — чистый урон (glass cannon)
 *   Туманный Страж         — гибрид уворот+крит (умеренный, а не крайний)
 *   Дух Павшего Знаменосца — эфемерный: живучесть + умеренный уворот/крит
 *   Костяной Часовой       — лёгкий трэш: слабая броня, низкий hp
 */
class WatchHillsAdditionalMonsterSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, lvl: int, hpMultiplier: float, armorMitigation: float,
     *     dodgePercent: float, critPercent: float, dmgPercent: float,
     *     expMultiplier: float, aggression: int, description: string,
     * }>
     */
    private const MONSTERS = [
        // --- Теневой Страж: чистый крит ---
        [
            'name' => 'Теневой Страж', 'lvl' => 38,
            'hpMultiplier' => 0.85, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 45,
            'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 70,
            'description' => 'Истукан, вытесанный из чёрного, будто впитавшего сумерки камня — контур едва различим на фоне тени. Молодой, бьёт из слепой зоны редко, но безошибочно.',
        ],
        [
            'name' => 'Теневой Страж', 'lvl' => 41,
            'hpMultiplier' => 0.88, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 52,
            'dmgPercent' => 10.8, 'expMultiplier' => 1.0, 'aggression' => 70,
            'description' => 'Истукан, вытесанный из чёрного, будто впитавшего сумерки камня — контур едва различим на фоне тени. Старый, каждый удар которого приходится точно в сустав или щель доспеха.',
        ],

        // --- Стальной Страж: сырой hp-танк без брони ---
        [
            'name' => 'Стальной Страж', 'lvl' => 38,
            'hpMultiplier' => 2.00, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 50,
            'description' => 'Истукан, скованный из выплавленных на перевале криц — не столько отражает удары, сколько попросту их выдерживает. Молодая отливка, ещё гулкая внутри.',
        ],
        [
            'name' => 'Стальной Страж', 'lvl' => 41,
            'hpMultiplier' => 2.10, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 10.8, 'expMultiplier' => 1.0, 'aggression' => 50,
            'description' => 'Истукан, скованный из выплавленных на перевале криц — не столько отражает удары, сколько попросту их выдерживает. Старейшая отливка, сплошной монолит без единой полости.',
        ],

        // --- Пепельный Страж: glass cannon ---
        [
            'name' => 'Пепельный Страж', 'lvl' => 38,
            'hpMultiplier' => 0.75, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 16.0, 'expMultiplier' => 1.0, 'aggression' => 80,
            'description' => 'Истукан из спёкшегося пепла старых костров Холмов — крошится от любого удара, но сам бьёт так, что за ним остаётся выжженная плешь. Молодой, ещё держащий форму между атаками.',
        ],
        [
            'name' => 'Пепельный Страж', 'lvl' => 41,
            'hpMultiplier' => 0.78, 'armorMitigation' => 0, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 16.8, 'expMultiplier' => 1.0, 'aggression' => 80,
            'description' => 'Истукан из спёкшегося пепла старых костров Холмов — крошится от любого удара, но сам бьёт так, что за ним остаётся выжженная плешь. Старый, тлеющий изнутри при каждом замахе.',
        ],

        // --- Туманный Страж: гибрид уворот+крит ---
        [
            'name' => 'Туманный Страж', 'lvl' => 38,
            'hpMultiplier' => 0.90, 'armorMitigation' => 0, 'dodgePercent' => 25, 'critPercent' => 20,
            'dmgPercent' => 10.8, 'expMultiplier' => 1.0, 'aggression' => 55,
            'description' => 'Истукан, чьи очертания стелющийся по Холмам туман скрадывает раньше, чем успеваешь прицелиться. Молодой, теряется в собственной дымке чаще, чем нападает.',
        ],
        [
            'name' => 'Туманный Страж', 'lvl' => 41,
            'hpMultiplier' => 0.93, 'armorMitigation' => 0, 'dodgePercent' => 28, 'critPercent' => 23,
            'dmgPercent' => 11.1, 'expMultiplier' => 1.0, 'aggression' => 55,
            'description' => 'Истукан, чьи очертания стелющийся по Холмам туман скрадывает раньше, чем успеваешь прицелиться. Старый, бьющий из марева ровно там, где его не ждут.',
        ],

        // --- Дух Павшего Знаменосца: эфемерный ---
        [
            'name' => 'Дух Павшего Знаменосца', 'lvl' => 38,
            'hpMultiplier' => 1.20, 'armorMitigation' => 0, 'dodgePercent' => 20, 'critPercent' => 10,
            'dmgPercent' => 11.5, 'expMultiplier' => 1.0, 'aggression' => 65,
            'description' => 'Полупрозрачная тень знаменосца, так и не выпустившего из рук древко — стяг истлел, но рука сжимается по старой памяти. Недавно поднявшийся, ещё помнящий своё имя.',
        ],
        [
            'name' => 'Дух Павшего Знаменосца', 'lvl' => 41,
            'hpMultiplier' => 1.25, 'armorMitigation' => 0, 'dodgePercent' => 23, 'critPercent' => 12,
            'dmgPercent' => 11.8, 'expMultiplier' => 1.0, 'aggression' => 65,
            'description' => 'Полупрозрачная тень знаменосца, так и не выпустившего из рук древко — стяг истлел, но рука сжимается по старой памяти. Древний, забывший даже за что когда-то пал.',
        ],

        // --- Костяной Часовой: лёгкий трэш ---
        [
            'name' => 'Костяной Часовой', 'lvl' => 38,
            'hpMultiplier' => 0.70, 'armorMitigation' => 0.18, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 10.0, 'expMultiplier' => 1.0, 'aggression' => 60,
            'description' => 'Костяк в остатках сторожевой брони, до сих пор несущий свой пост на Холмах. Свежая кладка костей, ещё скреплённая сухожилиями.',
        ],
        [
            'name' => 'Костяной Часовой', 'lvl' => 41,
            'hpMultiplier' => 0.73, 'armorMitigation' => 0.20, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 10.3, 'expMultiplier' => 1.0, 'aggression' => 60,
            'description' => 'Костяк в остатках сторожевой брони, до сих пор несущий свой пост на Холмах. Старая кладка, стёртая ветром до блеска, но держащая строй.',
        ],
    ];

    public function run(): void
    {
        $created = 0;

        foreach (self::MONSTERS as $p) {
            [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($p['lvl'], $p['dmgPercent']);
            $exp = MonsterStatFormulas::expReward($p['lvl'], $p['expMultiplier']);
            [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $p['lvl']);

            $monster = LocalizedContentSeederQuery::firstOrNewFor(Monster::class, ['name' => $p['name'], 'lvl' => $p['lvl']]);

            if ($monster->exists) {
                continue;
            }

            $monster->description = $p['description'];
            $monster->hp = MonsterStatFormulas::hp($p['lvl'], $p['hpMultiplier']);
            $monster->armor = MonsterStatFormulas::armorForMitigation($p['lvl'], $p['armorMitigation']);
            $monster->dodge = MonsterStatFormulas::rawStatForChance($p['lvl'], $p['dodgePercent']);
            $monster->critical = MonsterStatFormulas::rawStatForChance($p['lvl'], $p['critPercent']);
            $monster->min_dmg = $minDmg;
            $monster->max_dmg = $maxDmg;
            $monster->aggression = $p['aggression'];
            $monster->exp = $exp;
            $monster->min_money = $minMoney;
            $monster->max_money = $maxMoney;
            $monster->is_boss = false;
            $monster->save();

            $created++;

            $this->command?->info(sprintf(
                'monster «%s» (lvl %d): hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                $monster->name, $monster->lvl, $monster->hp, $monster->armor, $monster->dodge,
                $monster->critical, $monster->min_dmg, $monster->max_dmg, $monster->exp,
                $monster->min_money, $monster->max_money,
            ));
        }

        $this->command?->info("WatchHillsAdditionalMonsterSeeder: создано монстров — {$created}");
    }
}
