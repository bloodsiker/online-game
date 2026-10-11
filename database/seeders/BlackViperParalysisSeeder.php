<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Вешает «Паралич» (effects.id=3, уже существующий control-эффект, до
 * этого ни одним монстром не использовался) на удар всех 3 ступеней
 * Чёрной Гадюки (Заваленная Штольня) — по прямому указанию пользователя:
 * 8% шанс на удар, 50 секунд длительности.
 *
 * power_percent не задаём (null) — у паралича нет периодического урона
 * (damage_scaling_type эффекта не читается для control-типов, см.
 * MonsterOnHitEffectService::periodicDamage()), только блокировка
 * действия игрока на expires_at (processPlayerEffects()).
 */
class BlackViperParalysisSeeder extends Seeder
{
    private const EFFECT_ID = 3; // «Паралич»

    private const CHANCE_PERCENT = 8;

    private const DURATION_SECONDS = 50;

    public function run(): void
    {
        $monsterIds = DB::table('monsters')->where('name', 'Чёрная Гадюка')->pluck('id');

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

        $this->command?->info("BlackViperParalysisSeeder: паралич привязан к {$attached} монстрам (из {$monsterIds->count()})");
    }
}
