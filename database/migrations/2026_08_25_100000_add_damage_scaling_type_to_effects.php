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
        Schema::table('effects', function (Blueprint $table): void {
            $table->string('damage_scaling_type', 32)
                ->default('hit_damage')
                ->after('active_type');
        });

        Schema::table('player_active_effects', function (Blueprint $table): void {
            $table->decimal('current_value', 12, 6)->nullable()->change();
            $table->decimal('tick_remainder', 12, 6)->default(0)->after('current_value');
        });

        DB::table('effects')
            ->where('slug', 'monster_bleed')
            ->update(['damage_scaling_type' => 'target_max_hp']);
    }

    public function down(): void
    {
        Schema::table('player_active_effects', function (Blueprint $table): void {
            $table->dropColumn('tick_remainder');
            $table->integer('current_value')->nullable()->change();
        });

        Schema::table('effects', function (Blueprint $table): void {
            $table->dropColumn('damage_scaling_type');
        });
    }
};
