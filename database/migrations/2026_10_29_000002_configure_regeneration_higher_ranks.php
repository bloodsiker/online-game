<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach ([
                ['slug' => 'regen_skill_ii', 'percent' => 45, 'duration' => 150, 'cooldown' => 180],
                ['slug' => 'regen_skill_iii', 'percent' => 60, 'duration' => 180, 'cooldown' => 210],
            ] as $rank) {
                $skillId = DB::table('magic_skills')->where('slug', $rank['slug'])->value('id');
                if ($skillId === null) {
                    continue;
                }

                $description = json_encode([
                    'ru' => "Восстанавливает {$rank['percent']}% максимального здоровья за {$rank['duration']} секунд. Действует в бою и вне боя. Откат — {$rank['cooldown']} секунд.",
                    'uk' => "Відновлює {$rank['percent']}% максимального здоров’я за {$rank['duration']} секунд. Діє в бою та поза боєм. Відновлення — {$rank['cooldown']} секунд.",
                ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

                DB::table('magic_skills')->where('id', $skillId)->update([
                    'cooldown' => $rank['cooldown'],
                    'description' => $description,
                    'updated_at' => now(),
                ]);

                $effectIds = DB::table('magic_skill_effects')->where('magic_skill_id', $skillId)
                    ->whereIn('effect_id', DB::table('effects')->where('active_type', 'regen')->select('id'))
                    ->pluck('effect_id');

                DB::table('effects')->whereIn('id', $effectIds)->update([
                    'damage_scaling_type' => 'target_max_hp',
                    'value_per_tick' => $rank['percent'],
                    'tick_interval' => 1,
                    'description' => $description,
                    'updated_at' => now(),
                ]);
                DB::table('magic_skill_effects')->where('magic_skill_id', $skillId)
                    ->whereIn('effect_id', $effectIds)->update([
                        'duration_seconds' => $rank['duration'],
                        'chance' => 100,
                    ]);
            }
        });
    }

    public function down(): void
    {
        // Preserve balance values subsequently configured in the admin.
    }
};
