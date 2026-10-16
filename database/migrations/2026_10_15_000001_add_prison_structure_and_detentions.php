<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TYPES = [
        'shop', 'auction', 'heal', 'warehouse', 'clan_warehouse', 'bank', 'clan_bank',
        'blacksmith', 'exchange', 'auction_exchange', 'clan_skill_hall', 'reputation_exchange',
        'barter_shop', 'workshop', 'tool_workshop', 'clan_boss', 'prison',
    ];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $types = "'".implode("','", self::TYPES)."'";
            DB::statement("ALTER TABLE structures MODIFY type ENUM({$types}) NULL DEFAULT 'shop'");
        }

        if (! Schema::hasTable('player_detentions')) {
            Schema::create('player_detentions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('prison_structure_id')->nullable()->constrained('structures')->nullOnDelete();
                $table->unsignedBigInteger('prison_location_id');
                $table->string('prison_name', 100);
                $table->foreignId('imposed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('user_name', 100);
                $table->string('imposed_by_name', 100)->nullable();
                $table->unsignedBigInteger('previous_location_id')->nullable();
                $table->text('reason');
                $table->timestamp('starts_at');
                $table->timestamp('release_at');
                $table->timestamp('released_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'released_at', 'release_at'], 'player_detentions_user_status_release_idx');
                $table->index(['prison_structure_id', 'released_at', 'release_at'], 'player_detentions_prison_status_release_idx');
            });
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $indexes = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'player_detentions')
                ->pluck('INDEX_NAME')
                ->unique();

            if (! $indexes->contains('player_detentions_user_status_release_idx')
                && ! $indexes->contains('player_detentions_user_id_released_at_release_at_index')) {
                Schema::table('player_detentions', function (Blueprint $table): void {
                    $table->index(['user_id', 'released_at', 'release_at'], 'player_detentions_user_status_release_idx');
                });
            }

            if (! $indexes->contains('player_detentions_prison_status_release_idx')
                && ! $indexes->contains('player_detentions_prison_structure_id_released_at_release_at_index')) {
                Schema::table('player_detentions', function (Blueprint $table): void {
                    $table->index(['prison_structure_id', 'released_at', 'release_at'], 'player_detentions_prison_status_release_idx');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('player_detentions');

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('structures')->where('type', 'prison')->update(['type' => 'shop']);
            $types = "'shop','auction','heal','warehouse','clan_warehouse','bank','clan_bank','blacksmith','exchange','auction_exchange','clan_skill_hall','reputation_exchange','barter_shop','workshop','tool_workshop','clan_boss'";
            DB::statement("ALTER TABLE structures MODIFY type ENUM({$types}) NULL DEFAULT 'shop'");
        }
    }
};
