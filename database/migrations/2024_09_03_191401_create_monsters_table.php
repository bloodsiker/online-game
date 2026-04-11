<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monsters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('lvl');
            $table->integer('hp');
            $table->integer('armor');
            $table->integer('dodge');
            $table->integer('critical');
            $table->double('min_dmg');
            $table->double('max_dmg');
            $table->smallInteger('aggression');
            $table->integer('exp')->default(0);
            $table->integer('min_money')->default(0);
            $table->integer('max_money')->default(0);
            $table->boolean('is_boss')->default(0);
            $table->timestamps();
        });

        Schema::create('monster_on_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('hp_now');
            $table->timestamp('last_regen_at')->nullable();
            $table->integer('hp_max');
            $table->tinyInteger('active')->default(1);
            $table->unsignedTinyInteger('aggression')->nullable();
            $table->boolean('is_drop_money')->default(0);
            $table->integer('current_phase')->default(1);
            $table->timestamps();

            $table->foreign('monster_id')->references('id')->on('monsters');
            $table->foreign('location_id')->references('id')->on('locations');
        });

        Schema::create('location_has_monsters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('monster_id');
            $table->unsignedTinyInteger('aggression')->nullable();

            $table->foreign('monster_id')->references('id')->on('monsters');
            $table->foreign('location_id')->references('id')->on('locations');
        });

        Schema::create('monster_has_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained('monsters')->onDelete('cascade');
            $table->foreignId('share_item_id')->constrained('share_items')->onDelete('cascade');
            $table->double('drop_chance', 8, 7);
            $table->smallInteger('min_count')->default(1);
            $table->smallInteger('max_count')->default(1);
            $table->timestamps();
        });

        Schema::create('boss_mechanics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained('monsters')->onDelete('cascade');
            $table->string('mechanic_type'); // 'phase_change', 'summon_adds', 'enrage'
            $table->integer('trigger_hp_percent')->nullable(); // При якому HP активується
            $table->integer('trigger_turn')->nullable(); // На якому ході
            $table->json('config')->nullable(); // Конфігурація механіки
            $table->integer('priority')->default(0); // Порядок виконання
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['monster_id', 'is_active']);
        });

        Schema::create('boss_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained('monsters')->onDelete('cascade');
            $table->integer('phase_number');
            $table->integer('hp_threshold'); // HP % для переходу в фазу
            $table->json('stats_modifiers')->nullable(); // Зміна характеристик
            $table->json('new_skills')->nullable(); // Нові скіли в фазі
            $table->json('removed_skills')->nullable(); // Видалені скіли
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boss_phases');
        Schema::dropIfExists('boss_mechanics');
        Schema::dropIfExists('monster_has_items');
        Schema::dropIfExists('location_has_monsters');
        Schema::dropIfExists('monster_on_locations');
        Schema::dropIfExists('monsters');
    }
};
