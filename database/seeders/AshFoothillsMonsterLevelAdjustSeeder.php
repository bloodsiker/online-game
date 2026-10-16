<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;

/**
 * Корректирует уровни 6 монстров «Пепельных Предгорий»
 * (AshFoothillsMonsterSeeder, id 317-322) — по прямому указанию
 * пользователя: Скорпион 35/38, Саламандра 37/40, Гиена 36/39
 * (раньше у всех троих было 35/40). Все статы пересчитаны через
 * MonsterStatFormulas с ТЕМИ ЖЕ целевыми % архетипа, что были заданы
 * при первом создании (hpMultiplier/mitigation/crit%/dmg% на вид) —
 * см. докблок AshFoothillsMonsterSeeder. magic_resistance — по тем же
 * линейным зависимостям, что использовались изначально (скорпион:
 * 10+1.579*(lvl-9); саламандра = скорпион+4; гиена = lvl).
 */
class AshFoothillsMonsterLevelAdjustSeeder extends Seeder
{
    public function run(): void
    {
        $updates = [
            317 => ['lvl' => 35, 'hp' => 282, 'armor' => 20, 'critical' => 37, 'min_dmg' => 26, 'max_dmg' => 49, 'magic_resistance' => 51, 'exp' => 2071, 'min_money' => 171, 'max_money' => 342],
            318 => ['lvl' => 38, 'hp' => 306, 'armor' => 22, 'critical' => 40, 'min_dmg' => 29, 'max_dmg' => 53, 'magic_resistance' => 56, 'exp' => 2342, 'min_money' => 177, 'max_money' => 355],
            319 => ['lvl' => 37, 'hp' => 268, 'armor' => 14, 'critical' => 63, 'min_dmg' => 28, 'max_dmg' => 52, 'magic_resistance' => 58, 'exp' => 2251, 'min_money' => 175, 'max_money' => 351],
            320 => ['lvl' => 40, 'hp' => 290, 'armor' => 15, 'critical' => 68, 'min_dmg' => 30, 'max_dmg' => 56, 'magic_resistance' => 63, 'exp' => 2530, 'min_money' => 181, 'max_money' => 363],
            321 => ['lvl' => 36, 'hp' => 319, 'armor' => 13, 'dodge' => 27, 'critical' => 27, 'min_dmg' => 30, 'max_dmg' => 56, 'magic_resistance' => 36, 'exp' => 2160, 'min_money' => 173, 'max_money' => 347],
            322 => ['lvl' => 39, 'hp' => 345, 'armor' => 15, 'dodge' => 30, 'critical' => 30, 'min_dmg' => 33, 'max_dmg' => 61, 'magic_resistance' => 39, 'exp' => 2436, 'min_money' => 179, 'max_money' => 359],
        ];

        $updated = 0;
        foreach ($updates as $monsterId => $data) {
            $updated += LocalizedContentSeederQuery::forTable('monsters')->where('id', $monsterId)->update([
                ...$data,
                'updated_at' => now(),
            ]);
        }

        $this->command?->info("AshFoothillsMonsterLevelAdjustSeeder: обновлено {$updated} монстров");
    }
}
