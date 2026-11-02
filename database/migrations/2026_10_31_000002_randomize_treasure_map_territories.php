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
        Schema::table('treasure_map_configs', function (Blueprint $table): void {
            // Legacy template territory is no longer used for destination generation.
            $table->unsignedBigInteger('map_id')->nullable()->change();
        });
        Schema::table('treasure_hunts', function (Blueprint $table): void {
            $table->foreignId('target_map_id')->nullable()->constrained('maps')->restrictOnDelete();
        });
        $territories = DB::table('locations')->pluck('map_id', 'id');
        DB::table('treasure_hunts')->orderBy('id')->chunkById(200, function ($hunts) use ($territories): void {
            foreach ($hunts as $hunt) {
                DB::table('treasure_hunts')->where('id', $hunt->id)->update(['target_map_id' => $territories[$hunt->target_location_id]]);
            }
        });
    }

    public function down(): void
    {
        foreach (DB::table('treasure_map_configs')->whereNull('map_id')->get() as $config) {
            $mapId = DB::table('treasure_hunts')->where('treasure_map_config_id', $config->id)->value('target_map_id')
                ?? DB::table('maps')->orderBy('id')->value('id');
            DB::table('treasure_map_configs')->where('id', $config->id)->update(['map_id' => $mapId]);
        }
        Schema::table('treasure_hunts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('target_map_id');
        });
        Schema::table('treasure_map_configs', function (Blueprint $table): void {
            $table->unsignedBigInteger('map_id')->nullable(false)->change();
        });
    }
};
