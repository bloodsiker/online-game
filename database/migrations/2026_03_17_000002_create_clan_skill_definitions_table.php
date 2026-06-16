<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clan_skill_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedTinyInteger('max_level')->default(5);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('clan_skill_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_skill_definition_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->unsignedTinyInteger('required_clan_level')->default(1);
            $table->unsignedInteger('required_bonus_points')->default(0);
            $table->foreignId('share_item_id')->nullable()->constrained('share_items')->nullOnDelete();
            $table->unsignedInteger('share_item_count')->nullable();
            $table->foreignId('magic_skill_id')->nullable()->constrained('magic_skills')->nullOnDelete();
            $table->unique(['clan_skill_definition_id', 'level']);
            $table->timestamps();
        });

        Schema::create('clan_learned_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clans')->cascadeOnDelete();
            $table->foreignId('clan_skill_definition_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('current_level')->default(1);
            $table->unique(['clan_id', 'clan_skill_definition_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clan_learned_skills');
        Schema::dropIfExists('clan_skill_levels');
        Schema::dropIfExists('clan_skill_definitions');
    }
};
