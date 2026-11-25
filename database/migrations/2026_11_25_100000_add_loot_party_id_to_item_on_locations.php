<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Лут, выпавший группе, закреплён на минуту за всеми её участниками.
        Schema::table('item_on_locations', function (Blueprint $table): void {
            $table->unsignedBigInteger('loot_party_id')->nullable()->after('loot_owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('item_on_locations', function (Blueprint $table): void {
            $table->dropColumn('loot_party_id');
        });
    }
};
