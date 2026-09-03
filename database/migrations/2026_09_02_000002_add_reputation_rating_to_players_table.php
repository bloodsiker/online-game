<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const REGULAR_MEDAL_RATING = [
        500 => 10,
        1000 => 20,
        2000 => 50,
        3000 => 100,
    ];

    private const FEAT_MEDAL_RATING = 300;

    public function up(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->unsignedBigInteger('reputation_rating')->default(0)->after('death')->index();
        });

        DB::table('players')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function (Collection $players): void {
                foreach ($players as $player) {
                    DB::table('players')
                        ->where('id', $player->id)
                        ->update(['reputation_rating' => $this->calculateRating((int) $player->id)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->dropIndex(['reputation_rating']);
            $table->dropColumn('reputation_rating');
        });
    }

    private function calculateRating(int $playerId): int
    {
        $completedQuestIds = DB::table('quest_players')
            ->where('player_id', $playerId)
            ->where('status', 'completed')
            ->pluck('quest_id')
            ->mapWithKeys(static fn ($questId): array => [(int) $questId => true]);

        return (int) DB::table('player_reputations as player_reputation')
            ->join('reputation_tiers as tier', 'tier.reputation_id', '=', 'player_reputation.reputation_id')
            ->where('player_reputation.player_id', $playerId)
            ->whereColumn('player_reputation.points', '>=', 'tier.min_points')
            ->get([
                'tier.min_points',
                'tier.medal_name',
                'tier.feat_quest_id',
                'tier.feat_medal_name',
            ])
            ->sum(function (object $tier) use ($completedQuestIds): int {
                $featCompleted = $tier->feat_quest_id
                    && $completedQuestIds->has((int) $tier->feat_quest_id);

                $regularMedalEarned = $tier->medal_name
                    && (! $tier->feat_quest_id || $tier->feat_medal_name || $featCompleted);

                $regularRating = $regularMedalEarned
                    ? (self::REGULAR_MEDAL_RATING[(int) $tier->min_points] ?? 0)
                    : 0;

                $featRating = $tier->feat_medal_name && $featCompleted
                    ? self::FEAT_MEDAL_RATING
                    : 0;

                return $regularRating + $featRating;
            });
    }
};
