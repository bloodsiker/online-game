<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Вешает «Разрыв брони» (effects.id=17, -12% armor — уже существующий
 * общий эффект, ранее использовался Троллем/Огром/Циклопом) на удар всех
 * 3 ступеней Могильного Стража (Заброшенное кладбище) — по прямому
 * указанию пользователя. Шанс/длительность — по тому же образцу, что и у
 * существующих пользователей эффекта: 12% шанс, 5 секунд.
 */
class GraveGuardianArmorBreakSeeder extends Seeder
{
    private const EFFECT_ID = 17; // «Разрыв брони»

    private const CHANCE_PERCENT = 12;

    private const DURATION_SECONDS = 5;

    public function run(): void
    {
        $monsterIds = DB::table('monsters')->where('name', 'Могильный Страж')->pluck('id');

        $attached = 0;
        foreach ($monsterIds as $monsterId) {
            $exists = DB::table('monster_effects')
                ->where('monster_id', $monsterId)
                ->where('effect_id', self::EFFECT_ID)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('monster_effects')->insert([
                'monster_id' => $monsterId,
                'effect_id' => self::EFFECT_ID,
                'chance' => self::CHANCE_PERCENT,
                'duration_seconds' => self::DURATION_SECONDS,
                'power_percent' => null,
                'trigger_on_hit' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $attached++;
        }

        $this->command?->info("GraveGuardianArmorBreakSeeder: разрыв брони привязан к {$attached} монстрам (из {$monsterIds->count()}: Могильный Страж)");
    }
}
