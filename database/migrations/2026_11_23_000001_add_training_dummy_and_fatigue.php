<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->boolean('is_training_dummy')->default(false)->after('does_not_regenerate_hp');
        });

        Schema::table('players', function (Blueprint $table): void {
            // Общая усталость игрока (тренировка, позже — добыча ресурсов).
            $table->unsignedSmallInteger('fatigue')->default(0);
            $table->timestamp('fatigue_updated_at')->nullable();
            $table->timestamp('sleep_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->dropColumn(['fatigue', 'fatigue_updated_at', 'sleep_until']);
        });

        Schema::table('monsters', function (Blueprint $table): void {
            $table->dropColumn('is_training_dummy');
        });
    }
};
