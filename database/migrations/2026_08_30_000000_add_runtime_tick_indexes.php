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
        Schema::table('player_active_effects', function (Blueprint $table): void {
            $table->timestamp('next_tick_at')->nullable()->after('last_tick_at')->index();
            $table->index('expires_at');
        });

        Schema::table('players', function (Blueprint $table): void {
            $table->index('last_regen_at');
        });

        DB::table('player_active_effects')
            ->update(['next_tick_at' => DB::raw('COALESCE(last_tick_at, applied_at, created_at)')]);
    }

    public function down(): void
    {
        Schema::table('player_active_effects', function (Blueprint $table): void {
            $table->dropIndex(['next_tick_at']);
            $table->dropIndex(['expires_at']);
            $table->dropColumn('next_tick_at');
        });

        Schema::table('players', function (Blueprint $table): void {
            $table->dropIndex(['last_regen_at']);
        });
    }
};
