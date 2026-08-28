<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reputation_tiers', function (Blueprint $table): void {
            $table->string('feat_medal_name')->nullable()->after('feat_description');
            $table->string('feat_medal_icon')->nullable()->after('feat_medal_name');
        });
    }

    public function down(): void
    {
        Schema::table('reputation_tiers', function (Blueprint $table): void {
            $table->dropColumn(['feat_medal_name', 'feat_medal_icon']);
        });
    }
};
