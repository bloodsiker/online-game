<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Database\Seeder;

/**
 * Монстры карты «Заросшая дорога» (map slug Vr8Qm2Lp5Z, локации 992-1115).
 * Только СОЗДАНИЕ мобов — расстановка по локациям (location_has_monsters /
 * monster_on_locations) делается отдельным шагом, здесь её нет намеренно.
 *
 * 4 вида, каждый проходит 3 ступени силы по мере углубления в карту — та же
 * логика, что у «Могильной Осы»/«Костяного Волка» в Забытом Кургане: один и
 * тот же архетип, статы растут вместе с уровнем, но не меняют роль в бою.
 * Уровни специально разнесены вперемешку (25 Волк, 26 Вепрь, 27 Сорокопут,
 * 28 Плющ, 29 Волк, …) — на любом отрезке дороги игрок встречает все четыре
 * контры, а не закрывает их по очереди целыми блоками.
 *
 * Роли (ни одна не повторяется дважды на карте):
 *   Терновый Волк      — чистый уворот (стая, огибает удары через подлесок)
 *   Хищный Плющ        — броня + крит (засадный хищник, бьёт в упор)
 *   Вепрь               — чистый урон (таран без уворота/крита)
 *   Терновый Сорокопут — уворот + крит (самый опасный и самый хрупкий из четырёх)
 *
 * Статы считаются формулами из MonsterStatFormulas (та же кривая, что у
 * калиброванного ряда 20→50 в RecalibrateLeveling::MONSTERS), не вбиты руками.
 * Входные % (armorMitigation/dodgePercent/critPercent) держатся ЗАМЕТНО НИЖЕ
 * потолка формулы (MAX_STAT_BONUS=70) — на 48% и выше rawStatForChance() уходит
 * в нелинейный разгон и даёт сырой стат в разы больше любого существующего
 * монстра в игре (проверено и отброшено при подборе Сорокопута).
 *
 * Идентичность записи — (name, lvl): один вид легитимно существует несколькими
 * строками на разных уровнях, поэтому firstOrCreate матчит по обоим полям,
 * а не только по имени.
 *
 * expMultiplier у всех 12 = 1.0 (не выше, как у Тир1/Тир2, где «сложные» мобы
 * вроде Огра/Виверны получают 1.55-1.9 бонусом сверху). ExperienceCurve::
 * killsPerLevel() рассчитывает норму убийств, ПРЕДПОЛАГАЯ множитель ровно 1.0
 * (см. referenceMonsterExp) — реальный фарм этой карты подтвердил, что
 * multiplier>1.0 у монстра даёт прокачку быстрее задуманного (на этом пуле
 * было 1.44x против intended pace, см. обсуждение 2026-08-14). Тир1/Тир2
 * этот перекос не трогаем — вне scope этой карты.
 */
class OvergrownRoadMonsterSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string,
     *     lvl: int,
     *     hpMultiplier: float,
     *     armorMitigation: float,
     *     dodgePercent: float,
     *     critPercent: float,
     *     dmgPercent: float,
     *     expMultiplier: float,
     *     aggression: int,
     *     description: string,
     * }>
     */
    private const MONSTERS = [
        // --- Терновый Волк: чистый уворот ---
        [
            'name' => 'Терновый Волк', 'lvl' => 25,
            'hpMultiplier' => 1.10, 'armorMitigation' => 0.05, 'dodgePercent' => 22, 'critPercent' => 4,
            'dmgPercent' => 10.0, 'expMultiplier' => 1.0, 'aggression' => 85,
            'description' => 'Лесной волк, вросший в живую изгородь Заросшей дороги — шипы проросли прямо сквозь шкуру, не убив зверя. Молодой, ещё легко теряется в чаще, но уже приучен к засаде.',
        ],
        [
            'name' => 'Терновый Волк', 'lvl' => 29,
            'hpMultiplier' => 1.12, 'armorMitigation' => 0.06, 'dodgePercent' => 25, 'critPercent' => 4,
            'dmgPercent' => 10.2, 'expMultiplier' => 1.0, 'aggression' => 85,
            'description' => 'Лесной волк, вросший в живую изгородь Заросшей дороги — шипы проросли прямо сквозь шкуру, не убив зверя. Вожак небольшой стаи, шипы на загривке торчат частоколом.',
        ],
        [
            'name' => 'Терновый Волк', 'lvl' => 33,
            'hpMultiplier' => 1.15, 'armorMitigation' => 0.07, 'dodgePercent' => 28, 'critPercent' => 5,
            'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 85,
            'description' => 'Лесной волк, вросший в живую изгородь Заросшей дороги — шипы проросли прямо сквозь шкуру, не убив зверя. Матёрый одиночка, обходящий дорогу годами — не осталось живого места без шрама или колючки.',
        ],

        // --- Хищный Плющ: броня + крит ---
        [
            'name' => 'Хищный Плющ', 'lvl' => 28,
            'hpMultiplier' => 1.35, 'armorMitigation' => 0.17, 'dodgePercent' => 0, 'critPercent' => 22,
            'dmgPercent' => 11.5, 'expMultiplier' => 1.0, 'aggression' => 70,
            'description' => 'Плотоядная лоза, заплетающая старые вехи и статуи вдоль дороги — неотличима от обычного кустарника, пока не схватит. Молодое растение, ждущее первую жертву у обочины.',
        ],
        [
            'name' => 'Хищный Плющ', 'lvl' => 32,
            'hpMultiplier' => 1.38, 'armorMitigation' => 0.19, 'dodgePercent' => 0, 'critPercent' => 25,
            'dmgPercent' => 11.8, 'expMultiplier' => 1.0, 'aggression' => 70,
            'description' => 'Плотоядная лоза, заплетающая старые вехи и статуи вдоль дороги — неотличима от обычного кустарника, пока не схватит. Разросшийся куст в человеческий рост, усеянный побелевшими костями прошлых жертв.',
        ],
        [
            'name' => 'Хищный Плющ', 'lvl' => 36,
            'hpMultiplier' => 1.40, 'armorMitigation' => 0.21, 'dodgePercent' => 0, 'critPercent' => 28,
            'dmgPercent' => 12.0, 'expMultiplier' => 1.0, 'aggression' => 70,
            'description' => 'Плотоядная лоза, заплетающая старые вехи и статуи вдоль дороги — неотличима от обычного кустарника, пока не схватит. Древняя поросль, оплетающая целый участок дороги — сама земля здесь пропитана гнилью.',
        ],

        // --- Вепрь: чистый урон ---
        [
            'name' => 'Вепрь', 'lvl' => 26,
            'hpMultiplier' => 1.40, 'armorMitigation' => 0.13, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 11.5, 'expMultiplier' => 1.0, 'aggression' => 90,
            'description' => 'Одичавший кабан, чья шкура давно срослась с колючими побегами дороги — таранит всё, что движется. Молодой самец, ещё не набравший полной ярости.',
        ],
        [
            'name' => 'Вепрь', 'lvl' => 31,
            'hpMultiplier' => 1.45, 'armorMitigation' => 0.14, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 12.0, 'expMultiplier' => 1.0, 'aggression' => 90,
            'description' => 'Одичавший кабан, чья шкура давно срослась с колючими побегами дороги — таранит всё, что движется. Крупный секач с обломанными о камни клыками.',
        ],
        [
            'name' => 'Вепрь', 'lvl' => 35,
            'hpMultiplier' => 1.50, 'armorMitigation' => 0.16, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 12.5, 'expMultiplier' => 1.0, 'aggression' => 90,
            'description' => 'Одичавший кабан, чья шкура давно срослась с колючими побегами дороги — таранит всё, что движется. Исполинский вожак стада, проламывающий подлесок одним рывком.',
        ],

        // --- Терновый Сорокопут: уворот + крит ---
        [
            'name' => 'Терновый Сорокопут', 'lvl' => 27,
            'hpMultiplier' => 1.12, 'armorMitigation' => 0.02, 'dodgePercent' => 18, 'critPercent' => 20,
            'dmgPercent' => 10.0, 'expMultiplier' => 1.0, 'aggression' => 85,
            'description' => 'Хищная птица, накалывающая добычу на шипы вдоль дороги — старая привычка, ставшая охотничьим ритуалом. Молодая особь, только обустраивающая свою кладовую из колючек.',
        ],
        [
            'name' => 'Терновый Сорокопут', 'lvl' => 30,
            'hpMultiplier' => 1.14, 'armorMitigation' => 0.02, 'dodgePercent' => 20, 'critPercent' => 22,
            'dmgPercent' => 10.2, 'expMultiplier' => 1.0, 'aggression' => 85,
            'description' => 'Хищная птица, накалывающая добычу на шипы вдоль дороги — старая привычка, ставшая охотничьим ритуалом. Опытный охотник, чья кладовая утыкана костями путников.',
        ],
        [
            'name' => 'Терновый Сорокопут', 'lvl' => 34,
            'hpMultiplier' => 1.15, 'armorMitigation' => 0.02, 'dodgePercent' => 20, 'critPercent' => 24,
            'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 85,
            'description' => 'Хищная птица, накалывающая добычу на шипы вдоль дороги — старая привычка, ставшая охотничьим ритуалом. Древний хищник — его терновые шипы увешаны черепами, будто трофеями.',
        ],
    ];

    public function run(): void
    {
        $created = 0;

        foreach (self::MONSTERS as $p) {
            [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($p['lvl'], $p['dmgPercent']);
            $exp = MonsterStatFormulas::expReward($p['lvl'], $p['expMultiplier']);
            [$minMoney, $maxMoney] = MonsterStatFormulas::moneyRange($exp);

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

        $this->command?->info("OvergrownRoadMonsterSeeder: создано монстров — {$created}");
    }
}
