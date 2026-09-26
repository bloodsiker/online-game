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
        Schema::table('world_events', function (Blueprint $table): void {
            $table->foreignId('influence_map_id')
                ->nullable()
                ->after('map_id')
                ->constrained('maps')
                ->nullOnDelete();
        });

        DB::table('world_events')
            ->whereNull('influence_map_id')
            ->update(['influence_map_id' => DB::raw('map_id')]);
    }

    public function down(): void
    {
        Schema::table('world_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('influence_map_id');
        });
    }
};
