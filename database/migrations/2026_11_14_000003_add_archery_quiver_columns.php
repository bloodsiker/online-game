<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Стрельба из лука: вместимость колчана (share_items.quiver_capacity) и
 * содержимое конкретного экземпляра колчана (items.quiver_arrow_*) —
 * в колчане лежат стрелы только одного типа.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->unsignedInteger('quiver_capacity')->nullable()->after('slot');
        });

        Schema::table('items', function (Blueprint $table): void {
            $table->foreignId('quiver_arrow_share_item_id')->nullable()->after('rune_slot_count')
                ->constrained('share_items')->nullOnDelete();
            $table->unsignedInteger('quiver_arrow_count')->default(0)->after('quiver_arrow_share_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('quiver_arrow_share_item_id');
            $table->dropColumn('quiver_arrow_count');
        });

        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropColumn('quiver_capacity');
        });
    }
};
