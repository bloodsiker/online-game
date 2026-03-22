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
            $table->enum('type', ['one_time', 'repeatable', 'main', 'clan']);
            $table->foreignId('start_npc_id')->constrained('npcs');
            $table->foreignId('complete_npc_id')->constrained('npcs');
            $table->foreignId('parent_quest_id')->nullable()->constrained('quests');
            $table->foreignId('after_quest_id')->nullable()->constrained('quests');
            $table->integer('reset_period')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_finish')->default(false);
            $table->timestamps();
        });

        Schema::create('quest_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->foreignId('complete_npc_id')->nullable()->constrained('npcs')->nullOnDelete();
            $table->unsignedTinyInteger('order')->default(1)->comment('Stage sequence number, starting from 1');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['quest_id', 'order']);
        });

        Schema::create('quest_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->enum('type', ['item', 'money', 'exp', 'reputation', 'location_access', 'clan_points']);
            $table->integer('amount');
            $table->foreignId('share_item_id')->nullable()->constrained('share_items');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('quest_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('quest_stages')->nullOnDelete();
            $table->foreignId('map_id')->nullable()->constrained('maps')->nullOnDelete();
            $table->enum('type', ['kill', 'collect', 'talk', 'deliver']);
            $table->enum('target_type', ['monster', 'item', 'npc']);
            $table->bigInteger('target_id');
            $table->unsignedBigInteger('share_item_id')->nullable()->comment('For collect type: the ShareItem that physically drops into the backpack');
            $table->integer('required_amount')->nullable();
            $table->float('drop_chance', 5, 2)->nullable()->comment('Drop chance in percent (0-100). Only used for collect type objectives.');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('quest_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players');
            $table->foreignId('quest_id')->constrained('quests');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'failed'])->default('not_started');
            $table->foreignId('current_stage_id')->nullable()->constrained('quest_stages')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reset_at')->nullable();
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
        Schema::dropIfExists('quest_stages');
        Schema::dropIfExists('quests');
        Schema::dropIfExists('nps');
    }
};
