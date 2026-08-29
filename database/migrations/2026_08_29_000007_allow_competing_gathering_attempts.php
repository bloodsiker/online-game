<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gathering_attempts', function (Blueprint $table): void {
            $table->dropForeign(['gathering_node_id']);
            $table->dropUnique(['gathering_node_id']);
        });

        Schema::table('gathering_attempts', function (Blueprint $table): void {
            $table->index('gathering_node_id');
            $table->foreign('gathering_node_id')->references('id')->on('gathering_nodes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gathering_attempts', function (Blueprint $table): void {
            $table->dropForeign(['gathering_node_id']);
            $table->dropIndex(['gathering_node_id']);
            $table->unique('gathering_node_id');
        });

        Schema::table('gathering_attempts', function (Blueprint $table): void {
            $table->foreign('gathering_node_id')->references('id')->on('gathering_nodes')->cascadeOnDelete();
        });
    }
};
