<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Database\Seeder;

/**
 * Монстры карты «Сторожевые Холмы» (map slug Gx7Qm2Vp9L, локации 1116-1278).
 * Только СОЗДАНИЕ мобов — расстановка отдельным шагом (WatchHillsMonsterPlacementSeeder).
 *
 * Восстановление утраченного контента квестовой линии «Пробуждённые стражи»
 * (квесты 209-210): исходные монстры этого имени были созданы в промежутке
 * 12-23 августа, который выпал из бэкапов и бинлогов при аварии БД 25 сентября
 * 2026 — оригинальные записи невосстановимы, воссоздаются заново под теми же
 * именами, что фигурируют в тексте уже восстановленных квестов.
 *
 * 2 вида, каждый в 2 ступенях силы — карта продолжает уровневый диапазон сразу
 * после «Заросшей дороги» (25-36).
 *
 * Роли:
 *   Земляной страж — чистая броня (каменный истукан, держит удар)
 *   Ветряной страж — чистый уворот (воздушная стихия, уклоняется от всего)
 *
 * Статы считаются формулами из MonsterStatFormulas, не вбиты руками.
 */
class WatchHillsMonsterSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, lvl: int, hpMultiplier: float, armorMitigation: float,
     *     dodgePercent: float, critPercent: float, dmgPercent: float,
     *     expMultiplier: float, aggression: int, description: string,
     * }>
     */
    private const MONSTERS = [
        // --- Земляной страж: чистая броня ---
        [
            'name' => 'Земляной страж', 'lvl' => 38,
            'hpMultiplier' => 1.55, 'armorMitigation' => 0.30, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 11.0, 'expMultiplier' => 1.0, 'aggression' => 60,
            'description' => 'Каменный истукан, веками простоявший недвижимо среди холмов — его приняли за обычный валун. Сейчас, разбуженный, медленно поднимается с земли, осыпая вокруг мелкий гранитный крошева.',
        ],
        [
            'name' => 'Земляной страж', 'lvl' => 41,
            'hpMultiplier' => 1.60, 'armorMitigation' => 0.33, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 11.3, 'expMultiplier' => 1.0, 'aggression' => 60,
            'description' => 'Каменный истукан, веками простоявший недвижимо среди холмов — его приняли за обычный валун. Старший из стражей, покрытый глубокими трещинами, в которых поблёскивают прожилки тёплого янтарного света.',
        ],

        // --- Ветряной страж: чистый уворот ---
        [
            'name' => 'Ветряной страж', 'lvl' => 38,
            'hpMultiplier' => 0.85, 'armorMitigation' => 0.0, 'dodgePercent' => 45, 'critPercent' => 0,
            'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 65,
            'description' => 'Сгусток застывшего воздуха в форме доспеха без хозяина — стоял безмолвным караулом на гряде столько же, сколько его каменный собрат. Пробуждённый, кружит вихрем, почти неразличимый на фоне неба.',
        ],
        [
            'name' => 'Ветряной страж', 'lvl' => 41,
            'hpMultiplier' => 0.88, 'armorMitigation' => 0.0, 'dodgePercent' => 52, 'critPercent' => 0,
            'dmgPercent' => 10.8, 'expMultiplier' => 1.0, 'aggression' => 65,
            'description' => 'Сгусток застывшего воздуха в форме доспеха без хозяина — стоял безмолвным караулом на гряде столько же, сколько его каменный собрат. Старший из стражей — воздух вокруг него звенит и режет, как невидимые клинки.',
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

        $this->command?->info("WatchHillsMonsterSeeder: создано монстров — {$created}");
    }
}
