<?php

declare(strict_types=1);

use App\Modules\Player\Domain\Services\PlayerRevivalService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * «Возрождение» — эффект на игроке после смерти. Само наложение делает
 * PlayerDeathFinalizer, а предмет из премиум-магазина (см. следующие
 * миграции) возвращает потерянный опыт, пока эффект активен.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('effects')->updateOrInsert(
            ['slug' => PlayerRevivalService::EFFECT_SLUG],
            [
                'name' => 'Возрождение',
                'type' => 'buff',
                'active_type' => null,
                'damage_scaling_type' => null,
                'description' => sprintf(
                    'Действует %d минут после гибели. Пока эффект активен, предметом из премиум-магазина '.
                    'можно вернуть опыт, потерянный при последней смерти.',
                    intdiv(PlayerRevivalService::DURATION_SECONDS, 60),
                ),
                'image' => null,
                'chance' => 0,
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
        DB::table('effects')->where('slug', PlayerRevivalService::EFFECT_SLUG)->delete();
    }
};
