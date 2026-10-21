<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ребаланс 4 исходных героических подвесок (927 «Сердце Пламени», 928 «Слеза
 * Океана», 929 «Искра Шторма», 930 «Жало Топи») — по прямому указанию
 * пользователя: у них не было требования к уровню вовсе, а часть статов
 * (929, 930) заметно отставала от реального бюджета героических предметов
 * 50 уровня в игре.
 *
 * Бюджет сверен с уже существующим героическим оружием 50 уровня (id
 * 972-983: Топор Разрушения, Кнут Теней, Молот Титана, Меч Возмездия,
 * Фолиант Багрового Инферно и т.д.) — их вторичные статы (помимо
 * attack_min/max) лежат в диапазоне ~25-70 в зависимости от типа стата.
 * Новые значения подвесок подровнены под нижнюю-среднюю границу этого
 * диапазона (подвеска не даёт attack_min/max, весь бюджет уходит во
 * вторичные статы):
 *
 *   927 critical 15→25, magic_attack 25→45, crit_damage 20→28
 *   928 armor 30→32, magic_resistance 25→30, hp_max 100→120
 *   929 agility 15→28, dodge 20→28, magic_critical 20→30
 *   930 intuition 20→28, magic_attack 25→55, hp_max 80→100
 *
 * Требование уровня (50) добавлено отдельной строкой в
 * share_item_requirements — такого требования раньше не было вовсе.
 */
return new class extends Migration
{
    private const REQUIRED_LEVEL = 50;

    /** @var list<array{itemId: int, oldStats: array<string,int>, newStats: array<string,int>}> */
    private const CHANGES = [
        ['itemId' => 927, 'oldStats' => ['critical' => 15, 'magic_attack' => 25, 'crit_damage' => 20], 'newStats' => ['critical' => 25, 'magic_attack' => 45, 'crit_damage' => 28]],
        ['itemId' => 928, 'oldStats' => ['armor' => 30, 'magic_resistance' => 25, 'hp_max' => 100], 'newStats' => ['armor' => 32, 'magic_resistance' => 30, 'hp_max' => 120]],
        ['itemId' => 929, 'oldStats' => ['agility' => 15, 'dodge' => 20, 'magic_critical' => 20], 'newStats' => ['agility' => 28, 'dodge' => 28, 'magic_critical' => 30]],
        ['itemId' => 930, 'oldStats' => ['intuition' => 20, 'magic_attack' => 25, 'hp_max' => 80], 'newStats' => ['intuition' => 28, 'magic_attack' => 55, 'hp_max' => 100]],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::CHANGES as $change) {
            foreach ($change['newStats'] as $statType => $value) {
                DB::table('share_item_stats')
                    ->where('share_item_id', $change['itemId'])
                    ->where('stat_type', $statType)
                    ->update(['value' => $value, 'updated_at' => $now]);
            }

            $hasLevelRequirement = DB::table('share_item_requirements')
                ->where('share_item_id', $change['itemId'])
                ->where('type', 'level')
                ->exists();

            if (! $hasLevelRequirement) {
                DB::table('share_item_requirements')->insert([
                    'share_item_id' => $change['itemId'],
                    'type' => 'level',
                    'stat_key' => null,
                    'skill_id' => null,
                    'min_value' => self::REQUIRED_LEVEL,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $now = now();

        foreach (self::CHANGES as $change) {
            foreach ($change['oldStats'] as $statType => $value) {
                DB::table('share_item_stats')
                    ->where('share_item_id', $change['itemId'])
                    ->where('stat_type', $statType)
                    ->update(['value' => $value, 'updated_at' => $now]);
            }

            DB::table('share_item_requirements')
                ->where('share_item_id', $change['itemId'])
                ->where('type', 'level')
                ->where('min_value', self::REQUIRED_LEVEL)
                ->delete();
        }
    }
};
