<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasure_map_configs', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->foreignId('share_item_id')->unique()->constrained('share_items')->restrictOnDelete();
            $table->foreignId('reputation_id')->constrained('reputations')->restrictOnDelete();
            $table->foreignId('reward_share_item_id')->constrained('share_items')->restrictOnDelete();
            $table->foreignId('map_id')->constrained('maps')->restrictOnDelete();
            $table->unsignedInteger('required_reputation')->default(0);
            $table->unsignedInteger('reputation_reward')->default(10);
            $table->unsignedSmallInteger('search_duration_seconds')->default(15);
            $table->decimal('success_chance_percent', 6, 3)->default(70);
            $table->json('clue');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('treasure_map_locations', function (Blueprint $table): void {
            $table->foreignId('treasure_map_config_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->primary(['treasure_map_config_id', 'location_id']);
        });
        Schema::create('treasure_reputation_bonuses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reputation_tier_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('search_chance_bonus_percent', 6, 3)->default(0);
            $table->decimal('search_speed_bonus_percent', 5, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('treasure_hunts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('map_item_id')->nullable()->unique()->constrained('items')->nullOnDelete();
            $table->foreignId('treasure_map_config_id')->constrained()->restrictOnDelete();
            $table->foreignId('target_location_id')->constrained('locations')->restrictOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->uuid('search_token')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completes_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('chance_snapshot', 6, 3)->nullable();
            $table->timestamp('found_at')->nullable();
            $table->foreignId('reward_item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->unsignedInteger('reputation_awarded')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'found_at', 'activated_at']);
            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasure_hunts');
        Schema::dropIfExists('treasure_reputation_bonuses');
        Schema::dropIfExists('treasure_map_locations');
        Schema::dropIfExists('treasure_map_configs');
    }
};
