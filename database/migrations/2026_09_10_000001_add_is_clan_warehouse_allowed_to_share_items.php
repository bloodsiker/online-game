<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->boolean('is_clan_warehouse_allowed')
                ->default(true)
                ->after('is_give')
                ->comment('Можно ли положить предмет в хранилище клана');
        });
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropColumn('is_clan_warehouse_allowed');
        });
    }
};
