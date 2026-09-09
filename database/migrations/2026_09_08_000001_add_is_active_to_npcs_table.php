<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('npcs', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('hide_location');
            $table->index(['location_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('npcs', function (Blueprint $table): void {
            $table->dropIndex(['location_id', 'is_active']);
            $table->dropColumn('is_active');
        });
    }
};
