<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dungeon_stages', function (Blueprint $table): void {
            $table->unsignedInteger('gold_budget')->nullable()->after('clan_cooldown_seconds')
                ->comment('Монет за полное убийство босса этажа; делится между участниками пропорционально урону, масштабируется по % снятого HP если боcс не добит');
        });
    }

    public function down(): void
    {
        Schema::table('dungeon_stages', function (Blueprint $table): void {
            $table->dropColumn('gold_budget');
        });
    }
};
