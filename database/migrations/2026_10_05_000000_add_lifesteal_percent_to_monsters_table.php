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
            // % от нанесённого удара, который монстр восстанавливает себе —
            // вампиризм, зеркало игрового RunePassiveType::VAMPIRISM, но для
            // самого моба (см. «Кровавый Слуга», MonsterAttackService).
            $table->unsignedTinyInteger('lifesteal_percent')->default(0)->after('aggression');
        });
    }

    public function down(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->dropColumn('lifesteal_percent');
        });
    }
};
