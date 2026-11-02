<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Боевые скиллы из 2026_10_26_000001 гейтились только уровнем «Колдовства».
 * Добавляет вторую ось требования — интеллект (сила заклинаний) и мудрость
 * (мана/маг.резист), по аналогии с тем как оружие гейтится силой/ловкостью.
 * Пороги умеренные (как у оружия — "Кастет «Мамонт»" на lvl20 требует
 * strength=12, не максимум возможного), не на пике доступных свободных очков.
 */
return new class extends Migration
{
    /** @var array<int, array{int:int, wis:int}> magic_skill_id → пороги */
    private const THRESHOLDS = [
        1 => ['int' => 1, 'wis' => 1],    // Огненный шар (T1)
        2 => ['int' => 1, 'wis' => 1],    // Ледяная стрела (T1)
        70 => ['int' => 15, 'wis' => 10],  // Молния (T2)
        71 => ['int' => 15, 'wis' => 10],  // Разряд слабости (T2)
        72 => ['int' => 30, 'wis' => 20],  // Огненный залп (T3)
        73 => ['int' => 30, 'wis' => 20],  // Укус яда (T3)
        74 => ['int' => 30, 'wis' => 20],  // Ледяной шип (T3)
        75 => ['int' => 55, 'wis' => 35],  // Кровавый клинок (T4)
        76 => ['int' => 55, 'wis' => 35],  // Разрушение доспеха (T4)
        77 => ['int' => 55, 'wis' => 35],  // Парализующий взгляд (T4)
        78 => ['int' => 85, 'wis' => 55],  // Испепеляющий вихрь (T5)
        79 => ['int' => 85, 'wis' => 55],  // Клич ужаса (T5)
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::THRESHOLDS as $skillId => $t) {
            foreach (['intelligence' => $t['int'], 'wisdom' => $t['wis']] as $statKey => $minValue) {
                $exists = DB::table('magic_skill_requirements')
                    ->where('magic_skill_id', $skillId)->where('type', 'stat')->where('stat_key', $statKey)
                    ->exists();

                if (! $exists) {
                    DB::table('magic_skill_requirements')->insert([
                        'magic_skill_id' => $skillId, 'type' => 'stat', 'stat_key' => $statKey,
                        'min_value' => $minValue, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('magic_skill_requirements')
            ->whereIn('magic_skill_id', array_keys(self::THRESHOLDS))
            ->where('type', 'stat')
            ->whereIn('stat_key', ['intelligence', 'wisdom'])
            ->delete();
    }
};
