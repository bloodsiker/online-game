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
        Schema::table('reputations', function (Blueprint $table): void {
            $table->unsignedSmallInteger('quest_cooldown_hours')->default(48)->after('npc_id');
        });

        // Городская стража: задание раз в сутки.
        DB::table('reputations')->where('id', 1)->update(['quest_cooldown_hours' => 24]);
    }

    public function down(): void
    {
        Schema::table('reputations', function (Blueprint $table): void {
            $table->dropColumn('quest_cooldown_hours');
        });
    }
};
