<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clan_skill_levels', function (Blueprint $table) {
            $table->dropColumn(['effect_type', 'effect_value']);
        });
    }

    public function down(): void
    {
        Schema::table('clan_skill_levels', function (Blueprint $table) {
            $table->string('effect_type')->after('share_item_count');
            $table->unsignedInteger('effect_value')->default(0)->after('effect_type');
        });
    }
};