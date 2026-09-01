<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('share_items', 'upgrade_to_share_item_id')) {
                $table->foreignId('upgrade_to_share_item_id')
                    ->nullable()
                    ->after('rarity')
                    ->constrained('share_items')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('share_items', 'upgrade_gold_cost')) {
                $table->unsignedInteger('upgrade_gold_cost')->default(0)->after('upgrade_to_share_item_id');
            }
        });

        if (! Schema::hasTable('share_item_upgrade_materials')) {
            Schema::create('share_item_upgrade_materials', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('share_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('required_share_item_id')->constrained('share_items')->cascadeOnDelete();
                $table->unsignedInteger('count')->default(1);
                $table->timestamps();

                $table->unique(['share_item_id', 'required_share_item_id'], 'si_upgrade_material_unique');
            });
        } else {
            // MySQL не откатывает DDL: нужен повторный запуск после ошибки на индексе.
            Schema::table('share_item_upgrade_materials', function (Blueprint $table): void {
                $table->unique(['share_item_id', 'required_share_item_id'], 'si_upgrade_material_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('share_item_upgrade_materials');

        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('upgrade_to_share_item_id');
            $table->dropColumn('upgrade_gold_cost');
        });
    }
};
