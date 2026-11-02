<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('structures', function (Blueprint $table): void {
            $table->foreignId('reputation_id')->nullable()->unique()->constrained('reputations')->nullOnDelete();
        });
        Schema::table('shop_items', function (Blueprint $table): void {
            $table->unsignedInteger('min_reputation_points')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('shop_items', fn (Blueprint $table) => $table->dropColumn('min_reputation_points'));
        Schema::table('structures', fn (Blueprint $table) => $table->dropConstrainedForeignId('reputation_id'));
    }
};
