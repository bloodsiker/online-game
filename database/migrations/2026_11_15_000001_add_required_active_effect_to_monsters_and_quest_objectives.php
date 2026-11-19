<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Эффект-гейт: монстр виден/атакуем только под действием эффекта,
 * квестовый предмет (collect) выпадает только под действием эффекта.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->unsignedBigInteger('required_active_effect_id')->nullable()->after('respawn_at');
            $table->foreign('required_active_effect_id')->references('id')->on('effects')->nullOnDelete();
        });

        Schema::table('quest_objectives', function (Blueprint $table): void {
            $table->unsignedBigInteger('required_active_effect_id')->nullable()->after('drop_chance');
            $table->foreign('required_active_effect_id')->references('id')->on('effects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quest_objectives', function (Blueprint $table): void {
            $table->dropForeign(['required_active_effect_id']);
            $table->dropColumn('required_active_effect_id');
        });

        Schema::table('monsters', function (Blueprint $table): void {
            $table->dropForeign(['required_active_effect_id']);
            $table->dropColumn('required_active_effect_id');
        });
    }
};
