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
            $table->unsignedInteger('duration_seconds')->default(0)->after('chance');
        });

        Schema::table('magic_skill_effects', function (Blueprint $table): void {
            $table->unsignedInteger('duration_seconds')->default(0)->after('chance');
        });

        DB::table('effects')
            ->select(['id', 'duration'])
            ->orderBy('id')
            ->each(function (object $effect): void {
                DB::table('monster_effects')
                    ->where('effect_id', $effect->id)
                    ->update(['duration_seconds' => max(0, (int) $effect->duration)]);

                DB::table('magic_skill_effects')
                    ->where('effect_id', $effect->id)
                    ->update(['duration_seconds' => max(0, (int) $effect->duration)]);
            });

        Schema::table('effects', function (Blueprint $table): void {
            $table->dropColumn('duration');
        });
    }

    public function down(): void
    {
        Schema::table('effects', function (Blueprint $table): void {
            $table->unsignedInteger('duration')->default(0)->after('chance');
        });

        DB::table('effects')
            ->select('id')
            ->orderBy('id')
            ->each(function (object $effect): void {
                $monsterDuration = (int) DB::table('monster_effects')
                    ->where('effect_id', $effect->id)
                    ->max('duration_seconds');
                $skillDuration = (int) DB::table('magic_skill_effects')
                    ->where('effect_id', $effect->id)
                    ->max('duration_seconds');

                DB::table('effects')
                    ->where('id', $effect->id)
                    ->update(['duration' => max($monsterDuration, $skillDuration)]);
            });

        Schema::table('magic_skill_effects', function (Blueprint $table): void {
            $table->dropColumn('duration_seconds');
        });

        Schema::table('monster_effects', function (Blueprint $table): void {
            $table->dropColumn('duration_seconds');
        });
    }
};
