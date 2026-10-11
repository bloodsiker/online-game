<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Монстры для «Ожившая окраина» (map 4, ранее вообще без монстров) и
 * «Пепельные Предгорья» (map 16) — по прямому указанию пользователя,
 * диапазон уровней 35-40 для обеих карт.
 *
 * 3 вида:
 *   - «Пепельный Скорпион» — НОВЫЕ старшие тиры (lvl35/40) уже
 *     существующего вида (lvl9/28 в Опустевшей Долине). Архетип
 *     (hpMultiplier≈1.0, mitigation≈3%, crit≈18%, dmg≈9% от HP игрока)
 *     реверс-инжинирен из двух существующих тиров через
 *     MonsterStatFormulas и сохранён один в один для новых тиров —
 *     magic_resistance (10@9, 40@28, формулы для него нет) продолжен
 *     той же линейной зависимостью (~1.58 за уровень).
 *   - «Пепельная Саламандра» — НОВЫЙ вид (огненная тема, подтверждено
 *     пользователем), ниже броня/HP, выше крит.
 *   - «Выжженная Гиена» — НОВЫЙ вид (стайный хищник, подтверждено
 *     пользователем), выше HP/агрессия, единственная с ненулевым dodge.
 *
 * Изображений нет (как и у прочих новых монстров этой сессии до
 * генерации арта) — image оставлен пустым.
 */
class AshFoothillsMonsterSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('monsters')->where('name', 'Пепельная Саламандра')->exists()) {
            $this->command?->info('AshFoothillsMonsterSeeder: уже существует, пропускаю.');

            return;
        }

        $now = now();

        $rows = [
            // Пепельный Скорпион — новые старшие тиры существующего вида
            [
                'name' => 'Пепельный Скорпион', 'lvl' => 35, 'hp' => 282, 'armor' => 20, 'dodge' => 0, 'critical' => 37,
                'min_dmg' => 26, 'max_dmg' => 49, 'magic_resistance' => 51, 'aggression' => 85, 'exp' => 2071, 'min_money' => 171, 'max_money' => 342,
                'description' => 'Матёрый скорпион-патриарх, обосновавшийся в горячих расщелинах предгорий — его панцирь плотнее, а яд разъедает рану не хуже кислоты.',
            ],
            [
                'name' => 'Пепельный Скорпион', 'lvl' => 40, 'hp' => 322, 'armor' => 23, 'dodge' => 0, 'critical' => 42,
                'min_dmg' => 30, 'max_dmg' => 56, 'magic_resistance' => 59, 'aggression' => 85, 'exp' => 2530, 'min_money' => 181, 'max_money' => 363,
                'description' => 'Древнейший из скорпионов предгорий, вросший панцирем в сам камень — немногие доживают до встречи с ним во второй раз.',
            ],
            // Пепельная Саламандра — новый вид
            [
                'name' => 'Пепельная Саламандра', 'lvl' => 35, 'hp' => 254, 'armor' => 13, 'dodge' => 0, 'critical' => 60,
                'min_dmg' => 26, 'max_dmg' => 49, 'magic_resistance' => 55, 'aggression' => 60, 'exp' => 2071, 'min_money' => 171, 'max_money' => 342,
                'description' => 'Ящерица с шершавой пепельно-серой чешуёй, облюбовавшая горячие расщелины предгорий — её укус оставляет на коже лёгкий ожог.',
            ],
            [
                'name' => 'Пепельная Саламандра', 'lvl' => 40, 'hp' => 290, 'armor' => 15, 'dodge' => 0, 'critical' => 68,
                'min_dmg' => 30, 'max_dmg' => 56, 'magic_resistance' => 63, 'aggression' => 60, 'exp' => 2530, 'min_money' => 181, 'max_money' => 363,
                'description' => 'Крупная саламандра, чья чешуя раскалена настолько, что от прикосновения к ней трескается сухая трава — безошибочно находит самые горячие трещины в склонах.',
            ],
            // Выжженная Гиена — новый вид
            [
                'name' => 'Выжженная Гиена', 'lvl' => 35, 'hp' => 310, 'armor' => 13, 'dodge' => 27, 'critical' => 27,
                'min_dmg' => 29, 'max_dmg' => 54, 'magic_resistance' => 35, 'aggression' => 90, 'exp' => 2071, 'min_money' => 171, 'max_money' => 342,
                'description' => 'Дикая гиена с опалённой, свалявшейся в комья шерстью — охотится стаями вдоль пепельных троп, бросаясь на всё, что движется.',
            ],
            [
                'name' => 'Выжженная Гиена', 'lvl' => 40, 'hp' => 354, 'armor' => 15, 'dodge' => 31, 'critical' => 31,
                'min_dmg' => 33, 'max_dmg' => 62, 'magic_resistance' => 40, 'aggression' => 90, 'exp' => 2530, 'min_money' => 181, 'max_money' => 363,
                'description' => 'Вожак гиеньей стаи, переживший не одну охоту — шрамы на его шкуре едва заметны под слоем окаменевшего пепла.',
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

        $this->command?->info('AshFoothillsMonsterSeeder: создано 6 монстров (3 вида × 2 тира, lvl35/40)');
    }
}
