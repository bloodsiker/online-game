<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('npc_dialogue_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_id')->constrained('npcs')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->boolean('is_start')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['npc_id', 'is_start', 'is_active']);
            $table->index(['npc_id', 'sort_order']);
        });

        Schema::create('npc_dialogue_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_dialogue_node_id')->constrained('npc_dialogue_nodes')->cascadeOnDelete();
            $table->foreignId('to_node_id')->nullable()->constrained('npc_dialogue_nodes')->nullOnDelete();
            $table->string('button_text');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['npc_dialogue_node_id', 'is_active', 'sort_order'], 'npc_dialogue_options_node_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('npc_dialogue_options');
        Schema::dropIfExists('npc_dialogue_nodes');
    }
};
