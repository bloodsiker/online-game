<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_on_locations', function (Blueprint $table): void {
            $table->string('interaction_type', 20)
                ->default('pickup')
                ->after('count')
                ->comment('Способ взаимодействия с предметом на локации: pickup или open_here');
        });

        DB::table('item_on_locations')
            ->join('items', 'items.id', '=', 'item_on_locations.item_id')
            ->join('share_items', 'share_items.id', '=', 'items.share_item_id')
            ->where('share_items.type', 'chest')
            ->update(['item_on_locations.interaction_type' => 'open_here']);
    }

    public function down(): void
    {
        Schema::table('item_on_locations', function (Blueprint $table): void {
            $table->dropColumn('interaction_type');
        });
    }
};
