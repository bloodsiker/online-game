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
            $table->string('innate_passive_type', 20)->nullable()->after('rune_stat_pool')
                ->comment('Встроенная пассивка оружия/брони (RunePassiveType), не требует руны в слоте');
            $table->unsignedTinyInteger('innate_passive_value')->nullable()->after('innate_passive_type');
        });
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropColumn(['innate_passive_type', 'innate_passive_value']);
        });
    }
};
