<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->boolean('is_auction_sellable')->default(false)->after('is_sell');
        });

        DB::table('share_items')->update(['is_auction_sellable' => DB::raw('is_sell')]);
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropColumn('is_auction_sellable');
        });
    }
};
