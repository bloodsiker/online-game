<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // count_monster остаётся максимумом; min_monster — минимум мобов после респауна (1 = как раньше).
        Schema::table('locations', function (Blueprint $table): void {
            $table->smallInteger('min_monster')->default(1)->after('count_monster');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table): void {
            $table->dropColumn('min_monster');
        });
    }
};
