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
        Schema::table('players', function (Blueprint $table): void {
            $table->unsignedInteger('regen_hp_start')->nullable()->after('last_regen_at');
            $table->unsignedInteger('regen_mp_start')->nullable()->after('regen_hp_start');
        });

        DB::table('players')->orderBy('id')->eachById(function (object $player): void {
            DB::table('players')->where('id', $player->id)->update([
                'last_regen_at' => now(),
                'regen_hp_start' => max(0, (int) $player->hp_now),
                'regen_mp_start' => max(0, (int) $player->mp_now),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->dropColumn(['regen_hp_start', 'regen_mp_start']);
        });
    }
};
