<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('effects', function (Blueprint $table): void {
            $table->string('damage_scaling_type', 32)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('effects', function (Blueprint $table): void {
            $table->string('damage_scaling_type', 32)->default('hit_damage')->nullable(false)->change();
        });
    }
};
