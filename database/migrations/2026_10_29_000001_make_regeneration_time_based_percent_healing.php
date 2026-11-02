<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $skill = DB::table('magic_skills')->where('id', 29)->where('slug', 'regen_skill')->first();
            if ($skill === null) {
                return;
            }

            $description = json_encode([
                'ru' => 'Восстанавливает 30% максимального здоровья за 120 секунд. Действует в бою и вне боя. Откат — 150 секунд.',
                'uk' => 'Відновлює 30% максимального здоров’я за 120 секунд. Діє в бою та поза боєм. Відновлення — 150 секунд.',
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            DB::table('magic_skills')->where('id', $skill->id)->update([
                'cooldown' => 150,
                'description' => $description,
                'updated_at' => now(),
            ]);

            $effectIds = DB::table('magic_skill_effects')->where('magic_skill_id', $skill->id)
                ->whereIn('effect_id', DB::table('effects')->where(fn ($query) => $query
                    ->where('active_type', 'regen')->orWhere('slug', 'regen'))->select('id'))
                ->pluck('effect_id');

            DB::table('effects')->whereIn('id', $effectIds)->update([
                'active_type' => 'regen',
                'damage_scaling_type' => 'target_max_hp',
                'value_per_tick' => 30,
                'tick_interval' => 1,
                'description' => $description,
                'updated_at' => now(),
            ]);
            DB::table('magic_skill_effects')->where('magic_skill_id', $skill->id)
                ->whereIn('effect_id', $effectIds)->update(['duration_seconds' => 120, 'chance' => 100]);
        });
    }

    public function down(): void
    {
        // Balance values are managed in the admin and must not be overwritten on rollback.
    }
};
