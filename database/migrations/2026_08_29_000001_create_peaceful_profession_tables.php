<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->unsignedInteger('gathering_time_seconds')->nullable()->after('skill_exp');
            $table->unsignedInteger('gathering_respawn_seconds')->nullable()->after('gathering_time_seconds');
            $table->foreignId('gathering_tool_share_item_id')
                ->nullable()
                ->after('gathering_respawn_seconds')
                ->constrained('share_items')
                ->nullOnDelete();
        });

        Schema::create('map_gathering_resources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedSmallInteger('max_active')->default(1);
            $table->unsignedTinyInteger('min_x')->default(8);
            $table->unsignedTinyInteger('max_x')->default(92);
            $table->unsignedTinyInteger('min_y')->default(16);
            $table->unsignedTinyInteger('max_y')->default(74);
            $table->timestamps();

            $table->unique(['map_id', 'share_item_id']);
        });

        Schema::create('gathering_nodes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_gathering_resource_id')
                ->constrained('map_gathering_resources')
                ->cascadeOnDelete();
            $table->decimal('x_percent', 5, 2);
            $table->decimal('y_percent', 5, 2);
            $table->timestamp('respawn_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('gathering_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->unique()->constrained('players')->cascadeOnDelete();
            $table->foreignId('gathering_node_id')->constrained('gathering_nodes')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->timestamp('completes_at');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });

        Schema::create('player_recipes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('share_recipe_id')->constrained('share_recipes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['player_id', 'share_recipe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_recipes');
        Schema::dropIfExists('gathering_attempts');
        Schema::dropIfExists('gathering_nodes');
        Schema::dropIfExists('map_gathering_resources');

        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('gathering_tool_share_item_id');
            $table->dropColumn(['gathering_time_seconds', 'gathering_respawn_seconds']);
        });
    }
};
