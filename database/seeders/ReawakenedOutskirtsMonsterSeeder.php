<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Монстры для «Ожившая окраина» (map 4) — по прямому указанию
 * пользователя: НЕ пепельная троица (та остаётся только в «Пепельных
 * Предгорьях», map 16), а два ДРУГИХ существующих вида из базы, новые
 * старшие тиры:
 *   - «Терновый Волк» (lvl25/29/33 в «Заросшая дорога») → lvl 36/38/40.
 *     Архетип у оригинала НЕ плоский, а плавно растёт с уровнем
 *     (mitigation 4.98%→7.07%, dodge% 22.1→28.0, dmg% 10.07→10.53 на
 *     lvl25→33) — тренд линейно продолжен на новые уровни, а не усреднён.
 *   - «Степной Шакал» (lvl13/32 в «Опустевшая Долина») → lvl 35/37/40.
 *     Архетип практически плоский (hpMult≈0.753, mitigation≈2.9%,
 *     dodge%≈24.0%, dmg%≈9.06%) — magic_resistance растёт линейно
 *     (16@13, 46@32 → наклон ≈1.579/уровень, тот же коэффициент, что и
 *     у «Пепельного Скорпиона»).
 *
 * Описания адаптированы под новую локацию (заросшая окраина вместо
 * «Заросшей дороги»/«Долины», где эти виды жили раньше).
 */
class ReawakenedOutskirtsMonsterSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('monsters')->where('name', 'Терновый Волк')->where('lvl', 36)->exists()) {
            $this->command?->info('ReawakenedOutskirtsMonsterSeeder: уже существует, пропускаю.');

            return;
        }

        $now = now();

        $rows = [
            [
                'name' => 'Терновый Волк', 'lvl' => 36, 'hp' => 339, 'armor' => 56, 'dodge' => 93, 'critical' => 0,
                'min_dmg' => 32, 'max_dmg' => 60, 'magic_resistance' => 0, 'aggression' => 85, 'exp' => 2160, 'min_money' => 173, 'max_money' => 347,
                'description' => 'Лесной волк, вросший в живой плющ одичавшей окраины — шипы и лозы проросли прямо сквозь шкуру, не убив зверя. Старый хищник, обходящий заросшие дворы годами.',
            ],
            [
                'name' => 'Терновый Волк', 'lvl' => 38, 'hp' => 362, 'armor' => 64, 'dodge' => 107, 'critical' => 0,
                'min_dmg' => 34, 'max_dmg' => 64, 'magic_resistance' => 0, 'aggression' => 85, 'exp' => 2342, 'min_money' => 177, 'max_money' => 355,
                'description' => 'Лесной волк, вросший в живой плющ одичавшей окраины — шипы и лозы проросли прямо сквозь шкуру, не убив зверя. Вожак разросшейся стаи — из-под шерсти торчат уже не отдельные шипы, а целые переплетённые лозы.',
            ],
            [
                'name' => 'Терновый Волк', 'lvl' => 40, 'hp' => 385, 'armor' => 72, 'dodge' => 123, 'critical' => 0,
                'min_dmg' => 37, 'max_dmg' => 68, 'magic_resistance' => 0, 'aggression' => 85, 'exp' => 2530, 'min_money' => 181, 'max_money' => 363,
                'description' => 'Лесной волк, вросший в живой плющ одичавшей окраины — шипы и лозы проросли прямо сквозь шкуру, не убив зверя. Патриарх стаи, настолько сросшийся с терновником, что уже не разобрать, где кончается зверь и начинается куст.',
            ],
            [
                'name' => 'Степной Шакал', 'lvl' => 35, 'hp' => 212, 'armor' => 19, 'dodge' => 60, 'critical' => 0,
                'min_dmg' => 27, 'max_dmg' => 49, 'magic_resistance' => 51, 'aggression' => 80, 'exp' => 2071, 'min_money' => 171, 'max_money' => 342,
                'description' => 'Поджарый хищник, охотящийся стаей среди заросших дворов и опустевших полей окраины — быстр и неутомим в погоне.',
            ],
            [
                'name' => 'Степной Шакал', 'lvl' => 37, 'hp' => 224, 'armor' => 20, 'dodge' => 63, 'critical' => 0,
                'min_dmg' => 28, 'max_dmg' => 52, 'magic_resistance' => 54, 'aggression' => 80, 'exp' => 2251, 'min_money' => 175, 'max_money' => 351,
                'description' => 'Поджарый хищник, охотящийся стаей среди заросших дворов и опустевших полей окраины. Опытный загонщик стаи, безошибочно находящий тропы между разросшимся кустарником.',
            ],
            [
                'name' => 'Степной Шакал', 'lvl' => 40, 'hp' => 242, 'armor' => 22, 'dodge' => 68, 'critical' => 0,
                'min_dmg' => 30, 'max_dmg' => 56, 'magic_resistance' => 59, 'aggression' => 80, 'exp' => 2530, 'min_money' => 181, 'max_money' => 363,
                'description' => 'Поджарый хищник, охотящийся стаей среди заросших дворов и опустевших полей окраины. Древний вожак, переживший не одно поколение стаи — шрамы от старых охот скрыты под свалявшейся шерстью.',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('monsters')->insert([
                'name' => $row['name'],
                'description' => $row['description'],
                'image' => null,
                'lvl' => $row['lvl'],
                'hp' => $row['hp'],
                'armor' => $row['armor'],
                'dodge' => $row['dodge'],
                'critical' => $row['critical'],
                'min_dmg' => $row['min_dmg'],
                'max_dmg' => $row['max_dmg'],
                'attack_type' => 'physical',
                'magic_attack' => 0,
                'magic_power_coefficient' => 0,
                'magic_resistance' => $row['magic_resistance'],
                'aggression' => $row['aggression'],
                'passive_type' => null,
                'passive_value' => null,
                'exp' => $row['exp'],
                'min_money' => $row['min_money'],
                'max_money' => $row['max_money'],
                'is_boss' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command?->info('ReawakenedOutskirtsMonsterSeeder: создано 6 монстров (2 вида × 3 тира)');
    }
}
