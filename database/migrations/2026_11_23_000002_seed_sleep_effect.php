<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('effects')->where('slug', 'sleep')->exists()) {
            return;
        }

        DB::table('effects')->insert([
            'name' => json_encode(['ru' => 'Сон', 'uk' => 'Сон'], JSON_UNESCAPED_UNICODE),
            'name_legacy' => 'Сон',
            'slug' => 'sleep',
            'type' => 'debuff',
            'active_type' => null,
            'description' => json_encode([
                'ru' => 'Вы уснули от переутомления: нельзя двигаться, сражаться и убегать, пока не проснётесь.',
                'uk' => 'Ви заснули від перевтоми: не можна рухатися, битися й тікати, доки не прокинетеся.',
            ], JSON_UNESCAPED_UNICODE),
            'description_legacy' => 'Вы уснули от переутомления: нельзя двигаться, сражаться и убегать, пока не проснётесь.',
            'chance' => 0, 'is_stackable' => 0, 'max_stacks' => 1, 'tick_interval' => 0, 'is_dispellable' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('effects')->where('slug', 'sleep')->delete();
    }
};
