<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clan_skill_levels', function (Blueprint $table) {
            $table->dropForeign(['stone_share_item_id']);
            $table->renameColumn('stone_share_item_id', 'share_item_id');
            $table->unsignedInteger('share_item_count')->default(1)->after('share_item_id');
            $table->foreign('share_item_id')->references('id')->on('share_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clan_skill_levels', function (Blueprint $table) {
            $table->dropForeign(['share_item_id']);
            $table->dropColumn('share_item_count');
            $table->renameColumn('share_item_id', 'stone_share_item_id');
            $table->foreign('stone_share_item_id')->references('id')->on('share_items')->nullOnDelete();
        });
    }
};