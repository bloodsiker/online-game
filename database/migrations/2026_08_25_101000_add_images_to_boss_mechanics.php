<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boss_mechanics', function (Blueprint $table): void {
            $table->string('image')->nullable()->after('mechanic_type');
        });
    }

    public function down(): void
    {
        Schema::table('boss_mechanics', function (Blueprint $table): void {
            $table->dropColumn('image');
        });
    }
};
