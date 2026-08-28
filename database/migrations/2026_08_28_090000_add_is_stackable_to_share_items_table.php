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
            $table->boolean('is_stackable')
                ->default(false)
                ->after('is_droppable')
                ->comment('Можно ли объединять одинаковые предметы в одну ячейку');
        });

        // Сохраняем историческое правило рюкзака: всё, кроме экипировки,
        // до появления явного признака складывалось в одну ячейку.
        DB::table('share_items')
            ->whereNotIn('type', ['weapon', 'shield', 'armor', 'belt', 'bag'])
            ->update(['is_stackable' => true]);
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropColumn('is_stackable');
        });
    }
};
