<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reputation_tiers', function (Blueprint $table): void {
            $table->unsignedSmallInteger('favor_duration_seconds')->nullable()->after('feat_favor_percent')
                ->comment('Сколько секунд суммарно тикает милость богов (яд/лечение) на этом тире — как monster_effects.duration_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('reputation_tiers', function (Blueprint $table): void {
            $table->dropColumn('favor_duration_seconds');
        });
    }
};
