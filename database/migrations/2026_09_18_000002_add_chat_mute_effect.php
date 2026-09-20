<?php

declare(strict_types=1);

use App\Modules\Moderation\Application\Services\ChatMuteEffectService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('effects')->updateOrInsert(
            ['slug' => ChatMuteEffectService::EFFECT_SLUG],
            [
                'name' => 'Проклятие молчания',
                'type' => 'debuff',
                'active_type' => null,
                'damage_scaling_type' => null,
                'description' => 'Персонаж не может отправлять сообщения в игровой чат.',
                'image' => '/main/images/gag.gif',
                'chance' => 100,
                'is_stackable' => false,
                'max_stacks' => 1,
                'tick_interval' => 1,
                'value_per_tick' => null,
                'stat_modifiers' => null,
                'is_dispellable' => false,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );
    }

    public function down(): void
    {
        DB::table('effects')
            ->where('slug', ChatMuteEffectService::EFFECT_SLUG)
            ->delete();
    }
};
