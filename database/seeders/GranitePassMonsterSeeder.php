<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Database\Seeder;

/**
 * Монстры карты «Гранитный Перевал» (map slug Gx7Qm2Vp9L, локации 1279-1529).
 * Только СОЗДАНИЕ мобов — расстановка отдельным шагом (GranitePassMonsterPlacementSeeder).
 *
 * Восстановление утраченного контента квестовой линии «Голоса гранита»
 * (квесты 214-216): исходный «Гранитный Голем» (id 100, 104 в утраченной
 * истории) создан в промежутке 12-23 августа, выпавшем из бэкапов и бинлогов
 * при аварии БД 25 сентября 2026 — оригинал невосстановим, воссоздаётся
 * заново под тем же именем. «Кристальный скарабей» — новый вид для второго
 * источника силы по тексту квеста 215 («другой природы»).
 *
 * 2 вида, каждый в 2 ступенях силы — карта продолжает уровневый диапазон
 * сразу после «Сторожевых Холмов» (38-41).
 *
 * Роли:
 *   Гранитный Голем     — чистая броня (тяжелее Земляного стража Холмов)
 *   Кристальный скарабей — уворот + крит (быстрый, хрупкий панцирь)
 */
class GranitePassMonsterSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, lvl: int, hpMultiplier: float, armorMitigation: float,
     *     dodgePercent: float, critPercent: float, dmgPercent: float,
     *     expMultiplier: float, aggression: int, description: string,
     * }>
     */
    private const MONSTERS = [
        // --- Гранитный Голем: чистая броня ---
        [
            'name' => 'Гранитный Голем', 'lvl' => 45,
            'hpMultiplier' => 1.65, 'armorMitigation' => 0.35, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 11.8, 'expMultiplier' => 1.0, 'aggression' => 55,
            'description' => 'Истукан из цельного куска перевала — грубо намеченные черты лица едва различимы под слоем лишайника. Внутри, под гранитной коркой, гудит спрятанное древнее ядро.',
        ],
        [
            'name' => 'Гранитный Голем', 'lvl' => 49,
            'hpMultiplier' => 1.70, 'armorMitigation' => 0.38, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 12.2, 'expMultiplier' => 1.0, 'aggression' => 55,
            'description' => 'Истукан из цельного куска перевала — грубо намеченные черты лица едва различимы под слоем лишайника. Старейший из перевала, весь испещрён рунами, стёртыми ветром почти до неразличимости.',
        ],

        // --- Кристальный скарабей: уворот + крит ---
        [
            'name' => 'Кристальный скарабей', 'lvl' => 45,
            'hpMultiplier' => 0.80, 'armorMitigation' => 0.05, 'dodgePercent' => 30, 'critPercent' => 35,
            'dmgPercent' => 11.0, 'expMultiplier' => 1.0, 'aggression' => 75,
            'description' => 'Панцирный обитатель расщелин перевала, чей хитин прорастает гранями горного хрусталя — на свету вспыхивает и слепит на миг перед атакой. Быстрый и колющий, как сама порода, что его породила.',
        ],
        [
            'name' => 'Кристальный скарабей', 'lvl' => 49,
            'hpMultiplier' => 0.84, 'armorMitigation' => 0.06, 'dodgePercent' => 36, 'critPercent' => 40,
            'dmgPercent' => 11.4, 'expMultiplier' => 1.0, 'aggression' => 75,
            'description' => 'Панцирный обитатель расщелин перевала, чей хитин прорастает гранями горного хрусталя — на свету вспыхивает и слепит на миг перед атакой. Старая особь, чей панцирь нарастил грани в руку толщиной.',
        ],
    ];

    public function run(): void
    {
        $created = 0;

        foreach (self::MONSTERS as $p) {
            [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($p['lvl'], $p['dmgPercent']);
            $exp = MonsterStatFormulas::expReward($p['lvl'], $p['expMultiplier']);
            [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $p['lvl']);

            $monster = Monster::firstOrNew(['name' => $p['name'], 'lvl' => $p['lvl']]);

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

        $this->command?->info("GranitePassMonsterSeeder: создано монстров — {$created}");
    }
}
