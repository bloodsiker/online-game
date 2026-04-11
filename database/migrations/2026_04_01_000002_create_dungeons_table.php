<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dungeons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('tier')->default(1);
            $table->enum('type', ['linear', 'survival', 'boss_rush'])->default('linear');
            $table->unsignedBigInteger('entry_share_item_id')->nullable();
            $table->unsignedTinyInteger('max_players')->default(1);
            $table->enum('cooldown_type', ['personal', 'global'])->default('personal');
            $table->unsignedInteger('cooldown_seconds')->default(86400);
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->unsignedTinyInteger('min_level')->default(1);
            $table->boolean('is_active')->default(true);

            // Карта и локации данжа
            $table->unsignedBigInteger('map_id')->nullable();
            $table->unsignedBigInteger('entry_location_id')->nullable();
            $table->unsignedBigInteger('first_location_id')->nullable();
            $table->unsignedBigInteger('exit_location_id')->nullable();
            $table->unsignedBigInteger('return_location_id')->nullable();
            $table->boolean('monster_respawn')->default(false);
            $table->unsignedTinyInteger('wave_count')->nullable();
            // Множитель опыта за каждого убитого монстра внутри данжа (1.00 = без бонуса)
            $table->decimal('xp_multiplier', 5, 2)->default(1.00);

            $table->timestamps();

            $table->foreign('entry_share_item_id')->references('id')->on('share_items')->onDelete('set null');
            $table->foreign('map_id')->references('id')->on('maps')->onDelete('set null');
            $table->foreign('entry_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('first_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('exit_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('return_location_id')->references('id')->on('locations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dungeons');
    }
};
