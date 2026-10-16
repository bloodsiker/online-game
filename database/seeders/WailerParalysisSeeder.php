<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Вешает «Паралич» (effects.id=3, тот же общий control-эффект, что и у
 * Чёрной Гадюки на Заваленной Штольне, см. BlackViperParalysisSeeder) на
 * удар всех 3 ступеней Плакальщицы (Заброшенное кладбище) — по прямому
 * указанию пользователя: 10% шанс на удар, 60 секунд длительности.
 */
class WailerParalysisSeeder extends Seeder
{
    private const EFFECT_ID = 3; // «Паралич»

    private const CHANCE_PERCENT = 10;

    private const DURATION_SECONDS = 60;

    public function run(): void
    {
        $monsterIds = LocalizedContentSeederQuery::forTable('monsters')->where('name', 'Плакальщица')->pluck('id');

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

        $this->command?->info("WailerParalysisSeeder: паралич привязан к {$attached} монстрам (из {$monsterIds->count()}: Плакальщица)");
    }
}
