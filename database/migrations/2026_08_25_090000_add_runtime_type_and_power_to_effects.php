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
            $table->string('active_type', 32)->nullable()->after('type');
        });

        Schema::table('monster_effects', function (Blueprint $table): void {
            $table->decimal('power_percent', 5, 2)->nullable()->after('chance');
        });

        foreach (['stun', 'paralysis', 'poison', 'bleed', 'burn', 'regen'] as $activeType) {
            DB::table('effects')
                ->where('slug', $activeType)
                ->update(['active_type' => $activeType]);
        }
    }

    public function down(): void
    {
        Schema::table('monster_effects', function (Blueprint $table): void {
            $table->dropColumn('power_percent');
        });

        Schema::table('effects', function (Blueprint $table): void {
            $table->dropColumn('active_type');
        });
    }
};
