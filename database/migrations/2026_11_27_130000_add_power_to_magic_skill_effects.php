<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magic_skill_effects', function (Blueprint $table): void {
            // Для периодического урона (ожог, яд, кровотечение): сколько всего нанесёт эффект за всю длительность.
            $table->decimal('power_percent', 6, 2)->nullable()->after('duration_seconds');
            // hit — % от урона удара; target_max_hp — % от макс. HP цели; NULL — по типу эффекта.
            $table->string('power_basis', 16)->nullable()->after('power_percent');
        });
    }

    public function down(): void
    {
        Schema::table('magic_skill_effects', function (Blueprint $table): void {
            $table->dropColumn(['power_percent', 'power_basis']);
        });
    }
};
