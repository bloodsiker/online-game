<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_post_stocks', function (Blueprint $table): void {
            $table->unsignedInteger('current_quantity')->default(15)->change();
            $table->unsignedInteger('max_quantity')->default(15)->change();
        });
        DB::table('trade_post_stocks')->update([
            'max_quantity' => 15,
            'current_quantity' => DB::raw('CASE WHEN current_quantity > 15 THEN 15 ELSE current_quantity END'),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('trade_post_stocks', function (Blueprint $table): void {
            $table->unsignedInteger('current_quantity')->default(30)->change();
            $table->unsignedInteger('max_quantity')->default(30)->change();
        });
        // Do not replenish goods already purchased by players.
        DB::table('trade_post_stocks')->where('max_quantity', 15)->update(['max_quantity' => 30]);
    }
};
