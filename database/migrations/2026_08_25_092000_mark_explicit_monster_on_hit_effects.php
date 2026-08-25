<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monster_effects', function (Blueprint $table): void {
            $table->boolean('trigger_on_hit')->default(false)->after('power_percent');
        });

        $signatureEffectIds = DB::table('effects')
            ->whereIn('slug', [
                'monster_bleed',
                'monster_poison',
                'monster_burn',
                'monster_armor_break',
                'monster_chill',
                'monster_weakness',
            ])
            ->pluck('id');

        DB::table('monster_effects')
            ->whereIn('effect_id', $signatureEffectIds)
            ->update(['trigger_on_hit' => true]);
    }

    public function down(): void
    {
        Schema::table('monster_effects', function (Blueprint $table): void {
            $table->dropColumn('trigger_on_hit');
        });
    }
};
