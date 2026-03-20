<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clan_skill_levels', function (Blueprint $table) {
            $table->unsignedInteger('share_item_count')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('clan_skill_levels', function (Blueprint $table) {
            $table->unsignedInteger('share_item_count')->default(1)->change();
        });
    }
};