<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (DB::table('premium_levels')->lockForUpdate()->get() as $level) {
                $bonuses = json_decode($level->bonuses, true, flags: JSON_THROW_ON_ERROR);
                $enabled = (int) $level->level >= 3;
                $bonuses['auto_loot'] ??= (int) $enabled;
                $bonuses['_enabled']['auto_loot'] ??= $enabled;
                DB::table('premium_levels')->where('level', $level->level)->update([
                    'bonuses' => json_encode($bonuses, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Preserve level configuration subsequently changed by the administrator.
    }
};
