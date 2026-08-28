<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clan_skill_levels', function (Blueprint $table): void {
            $table->unsignedInteger('required_money')
                ->default(0)
                ->after('required_bonus_points')
                ->comment('Монеты, которые персонаж платит за изучение уровня кланового навыка');
        });
    }

    public function down(): void
    {
        Schema::table('clan_skill_levels', function (Blueprint $table): void {
            $table->dropColumn('required_money');
        });
    }
};
