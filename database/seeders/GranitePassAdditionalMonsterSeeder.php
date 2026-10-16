<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;

/**
 * Дополнительные виды монстров карты «Гранитный Перевал» (локации 1279-1529),
 * восстановленные по памяти пользователя после аварии БД 25 сентября 2026:
 * «Каменный Голем», «Горный Грифон», «Ледяной Волк» существовали в утраченной
 * базе, но не попали ни в один сохранившийся binlog — стат-лайны воссозданы
 * заново по формулам MonsterStatFormulas, откалиброванным на уже
 * восстановленных «Гранитный Голем»/«Кристальный скарабей» тех же уровней
 * (см. GranitePassMonsterSeeder).
 *
 * Только СОЗДАНИЕ мобов — расстановка в location_has_monsters отдельным шагом.
 *
 * Роли:
 *   Каменный Голем — младший родич Гранитного Голема: тот же профиль чистой
 *                    брони, но слабее (меньше hp/брони/урона)
 *   Горный Грифон  — воздушный хищник: сильнейший урон в зоне, заметные
 *                    уворот и крит, лёгкая броня
 *   Ледяной Волк   — стайный хищник: лёгкая броня, хороший уворот, высокая
 *                    агрессия
 */
class GranitePassAdditionalMonsterSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string, lvl: int, hpMultiplier: float, armorMitigation: float,
     *     dodgePercent: float, critPercent: float, dmgPercent: float,
     *     expMultiplier: float, aggression: int, description: string,
     * }>
     */
    private const MONSTERS = [
        // --- Каменный Голем: младший родич Гранитного Голема ---
        [
            'name' => 'Каменный Голем', 'lvl' => 45,
            'hpMultiplier' => 1.45, 'armorMitigation' => 0.28, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 10.5, 'expMultiplier' => 1.0, 'aggression' => 55,
            'description' => 'Истукан попроще гранитного собрата — сложен из обычного песчаника перевала, трещины на теле видны без труда. Молодая кладка, ещё держащая форму на честном слове — крошится при каждом ударе.',
        ],
        [
            'name' => 'Каменный Голем', 'lvl' => 49,
            'hpMultiplier' => 1.50, 'armorMitigation' => 0.31, 'dodgePercent' => 0, 'critPercent' => 0,
            'dmgPercent' => 10.9, 'expMultiplier' => 1.0, 'aggression' => 55,
            'description' => 'Истукан попроще гранитного собрата — сложен из обычного песчаника перевала, трещины на теле видны без труда. Многолетний страж перевала — трещины давно заросли мхом и минеральными наростами, укрепившими тело.',
        ],

        // --- Горный Грифон: воздушный хищник ---
        [
            'name' => 'Горный Грифон', 'lvl' => 45,
            'hpMultiplier' => 1.15, 'armorMitigation' => 0.12, 'dodgePercent' => 18, 'critPercent' => 16,
            'dmgPercent' => 13.5, 'expMultiplier' => 1.0, 'aggression' => 85,
            'description' => 'Хищная птица размером с быка, гнездящаяся на неприступных уступах перевала — высматривает добычу с высоты, прежде чем упасть камнем вниз. Молодая особь, ещё не покинувшая родное гнездовье, но уже опасная в воздухе.',
        ],
        [
            'name' => 'Горный Грифон', 'lvl' => 49,
            'hpMultiplier' => 1.20, 'armorMitigation' => 0.14, 'dodgePercent' => 22, 'critPercent' => 20,
            'dmgPercent' => 14.0, 'expMultiplier' => 1.0, 'aggression' => 85,
            'description' => 'Хищная птица размером с быка, гнездящаяся на неприступных уступах перевала — высматривает добычу с высоты, прежде чем упасть камнем вниз. Старый вожак стаи, чьи когти исцарапали не один щит путников, рискнувших пройти перевал.',
        ],

        // --- Ледяной Волк: стайный хищник ---
        [
            'name' => 'Ледяной Волк', 'lvl' => 45,
            'hpMultiplier' => 0.95, 'armorMitigation' => 0.08, 'dodgePercent' => 22, 'critPercent' => 12,
            'dmgPercent' => 12.0, 'expMultiplier' => 1.0, 'aggression' => 90,
            'description' => 'Волк с шерстью, покрытой не сходящей ледяной коркой — дыхание перевала выстудило кровь этого зверя навсегда. Молодой самец, охотящийся в одиночку у границ стаи.',
        ],
        [
            'name' => 'Ледяной Волк', 'lvl' => 49,
            'hpMultiplier' => 0.99, 'armorMitigation' => 0.09, 'dodgePercent' => 26, 'critPercent' => 15,
            'dmgPercent' => 12.4, 'expMultiplier' => 1.0, 'aggression' => 90,
            'description' => 'Волк с шерстью, покрытой не сходящей ледяной коркой — дыхание перевала выстудило кровь этого зверя навсегда. Вожак стаи, чьё дыхание оставляет иней даже на камне.',
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

        $this->command?->info("GranitePassAdditionalMonsterSeeder: создано монстров — {$created}");
    }
}
