<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Индексы под горячие пути квестов:
 *  - quest_players: уникальность (player_id, quest_id) закрывает race-condition
 *    двойного взятия квеста и ускоряет фильтр "квесты игрока" (килл-путь, НПС);
 *  - quest_clan_progress: композиты под частые фильтры по статусу.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Защита от дублей, накопившихся до введения ограничения:
        // оставляем самую раннюю запись игрока по квесту.
        $duplicates = DB::table('quest_players AS qp1')
            ->join('quest_players AS qp2', function ($join) {
                $join->on('qp1.player_id', '=', 'qp2.player_id')
                    ->on('qp1.quest_id', '=', 'qp2.quest_id')
                    ->whereColumn('qp1.id', '>', 'qp2.id');
            })
            ->pluck('qp1.id');

        if ($duplicates->isNotEmpty()) {
            DB::table('quest_player_objectives')
                ->whereIn('quest_player_id', $duplicates)
                ->delete();
            DB::table('quest_players')->whereIn('id', $duplicates)->delete();
        }

        Schema::table('quest_players', function (Blueprint $table) {
            $table->unique(['player_id', 'quest_id']);
        });

        Schema::table('quest_clan_progress', function (Blueprint $table) {
            $table->index(['clan_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('quest_players', function (Blueprint $table) {
            $table->dropUnique(['player_id', 'quest_id']);
        });

        Schema::table('quest_clan_progress', function (Blueprint $table) {
            $table->dropIndex(['clan_id', 'status']);
            $table->dropIndex(['user_id', 'status']);
        });
    }
};
