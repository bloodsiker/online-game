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
        Schema::create('npcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::table('structures', function (Blueprint $table) {
            $table->foreignId('npc_id')->nullable()->after('location_id')->constrained('npcs')->nullOnDelete();
        });

        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['one_time', 'repeatable', 'main']);
            $table->foreignId('start_npc_id')->constrained('npcs');
            $table->foreignId('complete_npc_id')->constrained('npcs');
            $table->foreignId('parent_quest_id')->nullable()->constrained('quests');
            $table->foreignId('after_quest_id')->nullable()->constrained('quests');
            $table->integer('reset_period')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_finish')->default(false);
            $table->timestamps();
        });

//        Schema::create('quest_steps', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('quest_id')->constrained();
//            $table->integer('step_number')->default(1);
//            $table->string('title');
//            $table->text('description');
//            $table->foreignId('start_npc_id')->constrained('npcs');
//            $table->foreignId('target_npc_id')->nullable()->constrained('npcs');
//            $table->foreignId('complete_npc_id')->constrained('npcs');
//            $table->enum('status', ['not_started', 'in_progress', 'completed', 'failed'])->default('not_started');
//            $table->boolean('is_active')->default(true);
//            $table->timestamps();
//        });

        Schema::create('quest_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->enum('type', ['item', 'money', 'experience', 'reputation']);
            $table->integer('amount');
            $table->foreignId('share_item_id')->nullable()->constrained('share_items');
            $table->timestamps();
        });

        Schema::create('quest_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->enum('type', ['kill', 'collect', 'talk', 'deliver']);
            $table->enum('target_type', ['monster', 'item', 'npc']);
            $table->bigInteger('target_id');
            $table->integer('required_amount')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('quest_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players');
            $table->foreignId('quest_id')->constrained('quests');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'failed'])->default('not_started');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reset_at')->nullable();
//            $table->integer('progress')->default(0);
            $table->timestamps();
        });

        Schema::create('quest_player_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_player_id')->constrained('quest_players')->cascadeOnDelete();
            $table->foreignId('quest_objective_id')->constrained('quest_objectives')->cascadeOnDelete();
            $table->integer('amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quest_player_objectives');
        Schema::dropIfExists('quest_players');
        Schema::dropIfExists('quest_objectives');
        Schema::dropIfExists('quest_rewards');
        Schema::dropIfExists('quests');
        Schema::dropIfExists('nps');
    }
};
