<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Сколько предметов даёт один успешный крафт по рецепту (стрелы делаются пачкой). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_recipes', function (Blueprint $table): void {
            $table->unsignedInteger('result_count')->default(1)->after('kraft_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('share_recipes', function (Blueprint $table): void {
            $table->dropColumn('result_count');
        });
    }
};
