<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;

/**
 * Создаёт 6 видов «Катакомб Жертвоприношений» (map_id=6, локации 511-818,
 * сейчас полностью пустая карта) по 4 ступени каждый — по прямому указанию
 * пользователя: уровни 40-60.
 *
 * Расстановка по локациям — отдельным шагом (ждём параметров респавна от
 * пользователя, как и для предыдущих карт), этот сидер только создаёт
 * сами monster-строки.
 */
class SacrificeCatacombsMonsterSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, levels: array{0:int,1:int,2:int,3:int},
     *     hpMul: float, armorMit: float, dodgeP: float, critP: float, dmgP: float, aggr: int,
     *     description: string,
     * }>
     */
    private const SPECIES = [
        [
            'name' => 'Кровавый Гончий', 'levels' => [40, 45, 50, 55],
            'hpMul' => 0.75, 'armorMit' => 0.05, 'dodgeP' => 28.0, 'critP' => 10.0, 'dmgP' => 11.0, 'aggr' => 90,
            'description' => 'Тощий пёс с содранной в нескольких местах шкурой, бегущий на запах крови быстрее, чем способен уследить взгляд — в катакомбах он всегда охотится стаей, даже если кажется, что он один.',
        ],
        [
            'name' => 'Кровавый Оракул', 'levels' => [42, 47, 52, 57],
            'hpMul' => 0.80, 'armorMit' => 0.05, 'dodgeP' => 10.0, 'critP' => 28.0, 'dmgP' => 11.0, 'aggr' => 55,
            'description' => 'Слепая фигура в рваном балахоне, читающая будущее по рисунку пролитой крови на камне — бьёт туда, где жертва окажется мгновение спустя, а не туда, где она стоит сейчас.',
        ],
        [
            'name' => 'Кровавый Палач', 'levels' => [44, 49, 54, 59],
            'hpMul' => 1.60, 'armorMit' => 0.20, 'dodgeP' => 0.0, 'critP' => 5.0, 'dmgP' => 13.0, 'aggr' => 80,
            'description' => 'Исполинская фигура в кожаном фартуке поверх голого торса, с тяжёлым колуном для разделки жертвенного скота — на людей он его тоже пускает, без разбора.',
        ],
        [
            'name' => 'Кровопийца-Инквизитор', 'levels' => [41, 46, 51, 56],
            'hpMul' => 1.00, 'armorMit' => 0.10, 'dodgeP' => 5.0, 'critP' => 15.0, 'dmgP' => 11.0, 'aggr' => 75,
            'description' => 'Бывший дознаватель культа, давно переставший довольствоваться одними лишь признаниями под пыткой — теперь он пьёт кровь тех, кого допрашивает, до последней капли.',
        ],
        [
            'name' => 'Пожиратель Грехов', 'levels' => [43, 48, 53, 58],
            'hpMul' => 1.70, 'armorMit' => 0.22, 'dodgeP' => 0.0, 'critP' => 0.0, 'dmgP' => 12.0, 'aggr' => 60,
            'description' => 'Раздувшаяся туша, некогда бывшая ритуальным служителем, — обычай велел ему принимать на себя грехи умерших, и за долгие годы катакомб он принял их слишком много, чтобы остаться человеком.',
        ],
        [
            'name' => 'Тень Приговорённого', 'levels' => [45, 50, 55, 60],
            'hpMul' => 0.65, 'armorMit' => 0.03, 'dodgeP' => 32.0, 'critP' => 12.0, 'dmgP' => 10.0, 'aggr' => 65,
            'description' => 'Еле видимый контур того, кого когда-то привели на алтарь против воли — не помнит ни своего имени, ни лица, помнит только приговор, который так и не принял.',
        ],
    ];

    public function run(): void
    {
        foreach (self::SPECIES as $species) {
            foreach ($species['levels'] as $level) {
                $existing = LocalizedContentSeederQuery::forTable('monsters')->where('name', $species['name'])->where('lvl', $level)->first();

                if ($existing !== null) {
                    $this->command?->info("{$species['name']} lvl{$level} уже существует (id={$existing->id}), пропускаю");

                    continue;
                }

                $hp = MonsterStatFormulas::hp($level, $species['hpMul']);
                $armor = MonsterStatFormulas::armorForMitigation($level, $species['armorMit']);
                $dodge = MonsterStatFormulas::rawStatForChance($level, $species['dodgeP']);
                $critical = MonsterStatFormulas::rawStatForChance($level, $species['critP']);
                [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($level, $species['dmgP']);
                $exp = MonsterStatFormulas::expReward($level, 1.0);
                [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp, $level);

                $monsterId = LocalizedContentSeederQuery::forTable('monsters')->insertGetId([
                    'name' => $species['name'],
                    'description' => $species['description'],
                    'image' => null,
                    'lvl' => $level,
                    'hp' => $hp,
                    'armor' => $armor,
                    'dodge' => $dodge,
                    'critical' => $critical,
                    'min_dmg' => $minDmg,
                    'max_dmg' => $maxDmg,
                    'attack_type' => 'physical',
                    'magic_attack' => 0,
                    'magic_power_coefficient' => 0,
                    'magic_resistance' => 0,
                    'aggression' => $species['aggr'],
                    'exp' => $exp,
                    'min_money' => $minMoney,
                    'max_money' => $maxMoney,
                    'is_boss' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command?->info(sprintf(
                    'создан %s lvl%d: hp=%d armor=%d dodge=%d crit=%d dmg=%d-%d exp=%d money=%d-%d',
                    $species['name'], $level, $hp, $armor, $dodge, $critical, $minDmg, $maxDmg, $exp, $minMoney, $maxMoney,
                ));
            }
        }
    }
}
