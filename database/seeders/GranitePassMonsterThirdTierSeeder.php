<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Database\Seeder;

/**
 * Расширяет диапазон «Гранитного Перевала» с 46-56 до 45-59 (по указанию
 * пользователя — «диапазон должен быть 45-60») и добавляет ТРЕТЬЮ (старшую)
 * ступень силы каждому из 5 видов — было 2 ступени/вид, стало 3.
 *
 * Существующие 2 ступени КАЖДОГО вида обновляются по месту (находит по
 * текущим name+lvl — id не теряются, «Гранитный Голем»/«Кристальный
 * скарабей» заданы по id в quest_objectives квестов 214/215) и получают
 * новые уровни; третья ступень — новая строка с продолжением того же
 * тренда роли (hpMultiplier/armorMitigation/dodgePercent/critPercent/
 * dmgPercent растут тем же шагом, что и между 1-й и 2-й ступенью).
 *
 * Новая раскладка уровней (было → стало):
 *   Каменный Голем        46,48    → 45,46,47
 *   Кристальный скарабей  47,50    → 48,49,50
 *   Ледяной Волк          49,52    → 51,52,53
 *   Гранитный Голем       51,54    → 54,55,56
 *   Горный Грифон         53,56    → 57,58,59
 */
class GranitePassMonsterThirdTierSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, currentLvl: ?int, newLvl: int, isNew: bool,
     *     hpMultiplier: float, armorMitigation: float, dodgePercent: float,
     *     critPercent: float, dmgPercent: float, aggression: int, description: string,
     * }>
     */
    private const MONSTERS = [
        // --- Каменный Голем: 45, 46, 47 ---
        ['name' => 'Каменный Голем', 'currentLvl' => 46, 'newLvl' => 45, 'isNew' => false, 'hpMultiplier' => 1.45, 'armorMitigation' => 0.28, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 10.5, 'aggression' => 55, 'description' => ''],
        ['name' => 'Каменный Голем', 'currentLvl' => 48, 'newLvl' => 46, 'isNew' => false, 'hpMultiplier' => 1.50, 'armorMitigation' => 0.31, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 10.9, 'aggression' => 55, 'description' => ''],
        ['name' => 'Каменный Голем', 'currentLvl' => null, 'newLvl' => 47, 'isNew' => true, 'hpMultiplier' => 1.55, 'armorMitigation' => 0.34, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 11.3, 'aggression' => 55, 'description' => 'Истукан попроще гранитного собрата — сложен из обычного песчаника перевала, трещины на теле видны без труда. Древний страж, чья кладка срослась с самой скалой перевала намертво.'],

        // --- Кристальный скарабей: 48, 49, 50 ---
        ['name' => 'Кристальный скарабей', 'currentLvl' => 47, 'newLvl' => 48, 'isNew' => false, 'hpMultiplier' => 0.80, 'armorMitigation' => 0.05, 'dodgePercent' => 30, 'critPercent' => 35, 'dmgPercent' => 11.0, 'aggression' => 75, 'description' => ''],
        ['name' => 'Кристальный скарабей', 'currentLvl' => 50, 'newLvl' => 49, 'isNew' => false, 'hpMultiplier' => 0.84, 'armorMitigation' => 0.06, 'dodgePercent' => 36, 'critPercent' => 40, 'dmgPercent' => 11.4, 'aggression' => 75, 'description' => ''],
        ['name' => 'Кристальный скарабей', 'currentLvl' => null, 'newLvl' => 50, 'isNew' => true, 'hpMultiplier' => 0.88, 'armorMitigation' => 0.07, 'dodgePercent' => 42, 'critPercent' => 45, 'dmgPercent' => 11.8, 'aggression' => 75, 'description' => 'Панцирный обитатель расщелин перевала, чей хитин прорастает гранями горного хрусталя — на свету вспыхивает и слепит на миг перед атакой. Древнейшая особь, чей панцирь превратился в сплошной клинок горного хрусталя.'],

        // --- Ледяной Волк: 51, 52, 53 ---
        ['name' => 'Ледяной Волк', 'currentLvl' => 49, 'newLvl' => 51, 'isNew' => false, 'hpMultiplier' => 0.95, 'armorMitigation' => 0.08, 'dodgePercent' => 22, 'critPercent' => 12, 'dmgPercent' => 12.0, 'aggression' => 90, 'description' => ''],
        ['name' => 'Ледяной Волк', 'currentLvl' => 52, 'newLvl' => 52, 'isNew' => false, 'hpMultiplier' => 0.99, 'armorMitigation' => 0.09, 'dodgePercent' => 26, 'critPercent' => 15, 'dmgPercent' => 12.4, 'aggression' => 90, 'description' => ''],
        ['name' => 'Ледяной Волк', 'currentLvl' => null, 'newLvl' => 53, 'isNew' => true, 'hpMultiplier' => 1.03, 'armorMitigation' => 0.10, 'dodgePercent' => 30, 'critPercent' => 18, 'dmgPercent' => 12.8, 'aggression' => 90, 'description' => 'Волк с шерстью, покрытой не сходящей ледяной коркой — дыхание перевала выстудило кровь этого зверя навсегда. Древний вожак стаи, чей иней не тает даже под лапами бегущего.'],

        // --- Гранитный Голем: 54, 55, 56 ---
        ['name' => 'Гранитный Голем', 'currentLvl' => 51, 'newLvl' => 54, 'isNew' => false, 'hpMultiplier' => 1.65, 'armorMitigation' => 0.35, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 11.8, 'aggression' => 55, 'description' => ''],
        ['name' => 'Гранитный Голем', 'currentLvl' => 54, 'newLvl' => 55, 'isNew' => false, 'hpMultiplier' => 1.70, 'armorMitigation' => 0.38, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 12.2, 'aggression' => 55, 'description' => ''],
        ['name' => 'Гранитный Голем', 'currentLvl' => null, 'newLvl' => 56, 'isNew' => true, 'hpMultiplier' => 1.75, 'armorMitigation' => 0.41, 'dodgePercent' => 0, 'critPercent' => 0, 'dmgPercent' => 12.6, 'aggression' => 55, 'description' => 'Истукан из цельного куска перевала — грубо намеченные черты лица едва различимы под слоем лишайника. Древнейший из перевала, весь перевал будто вырос вокруг его неподвижных ног.'],

        // --- Горный Грифон: 57, 58, 59 ---
        ['name' => 'Горный Грифон', 'currentLvl' => 53, 'newLvl' => 57, 'isNew' => false, 'hpMultiplier' => 1.15, 'armorMitigation' => 0.12, 'dodgePercent' => 18, 'critPercent' => 16, 'dmgPercent' => 13.5, 'aggression' => 85, 'description' => ''],
        ['name' => 'Горный Грифон', 'currentLvl' => 56, 'newLvl' => 58, 'isNew' => false, 'hpMultiplier' => 1.20, 'armorMitigation' => 0.14, 'dodgePercent' => 22, 'critPercent' => 20, 'dmgPercent' => 14.0, 'aggression' => 85, 'description' => ''],
        ['name' => 'Горный Грифон', 'currentLvl' => null, 'newLvl' => 59, 'isNew' => true, 'hpMultiplier' => 1.25, 'armorMitigation' => 0.16, 'dodgePercent' => 26, 'critPercent' => 24, 'dmgPercent' => 14.5, 'aggression' => 85, 'description' => 'Хищная птица размером с быка, гнездящаяся на неприступных уступах перевала — высматривает добычу с высоты, прежде чем упасть камнем вниз. Древний владыка перевала — под его крылом умещается тень, в которой тонет целый отряд.'],
    ];

    public function run(): void
    {
        $updated = 0;
        $created = 0;

        foreach (self::MONSTERS as $m) {
            [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($m['newLvl'], $m['dmgPercent']);
            $exp = MonsterStatFormulas::expReward($m['newLvl'], 1.0);
            [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $m['newLvl']);

            if ($m['isNew']) {
                $monster = Monster::firstOrNew(['name' => $m['name'], 'lvl' => $m['newLvl']]);
                if ($monster->exists) {
                    continue;
                }
                $monster->description = $m['description'];
                $monster->is_boss = false;
            } else {
                $monster = Monster::query()->where('name', $m['name'])->where('lvl', $m['currentLvl'])->first();
                if ($monster === null) {
                    $this->command?->warn(sprintf('не найден: «%s» lvl %d', $m['name'], $m['currentLvl']));

                    continue;
                }
                $monster->lvl = $m['newLvl'];
            }

            $monster->hp = MonsterStatFormulas::hp($m['newLvl'], $m['hpMultiplier']);
            $monster->armor = MonsterStatFormulas::armorForMitigation($m['newLvl'], $m['armorMitigation']);
            $monster->dodge = MonsterStatFormulas::rawStatForChance($m['newLvl'], $m['dodgePercent']);
            $monster->critical = MonsterStatFormulas::rawStatForChance($m['newLvl'], $m['critPercent']);
            $monster->min_dmg = $minDmg;
            $monster->max_dmg = $maxDmg;
            $monster->aggression = $m['aggression'];
            $monster->exp = $exp;
            $monster->min_money = $minMoney;
            $monster->max_money = $maxMoney;
            $monster->save();

            $m['isNew'] ? $created++ : $updated++;

            $this->command?->info(sprintf(
                '%s «%s» lvl %d: hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                $m['isNew'] ? 'создан' : 'обновлён', $monster->name, $monster->lvl, $monster->hp, $monster->armor,
                $monster->dodge, $monster->critical, $monster->min_dmg, $monster->max_dmg,
                $monster->exp, $monster->min_money, $monster->max_money,
            ));
        }

        $this->command?->info("GranitePassMonsterThirdTierSeeder: обновлено — {$updated}, создано — {$created}");
    }
}
