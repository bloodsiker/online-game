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
        Schema::table('share_item_lock_configs', function (Blueprint $table): void {
            $table->renameColumn('trap_level', 'trap_chance_penalty_percent');
        });

        DB::table('share_item_lock_configs')->update([
            'trap_chance_penalty_percent' => DB::raw('CASE trap_chance_penalty_percent WHEN 1 THEN 5 WHEN 2 THEN 12 WHEN 3 THEN 20 ELSE trap_chance_penalty_percent END'),
        ]);
    }

    public function down(): void
    {
        DB::table('share_item_lock_configs')->update([
            'trap_chance_penalty_percent' => DB::raw('CASE trap_chance_penalty_percent WHEN 5 THEN 1 WHEN 12 THEN 2 WHEN 20 THEN 3 ELSE 0 END'),
        ]);

        Schema::table('share_item_lock_configs', function (Blueprint $table): void {
            $table->renameColumn('trap_chance_penalty_percent', 'trap_level');
        });
    }
};
