<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\Services;

use App\Enums\QuestPlayerStatus;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayerObjective;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    public function getOrCreate(Player $player, Reputation $reputation): PlayerReputation
    {
        return PlayerReputation::firstOrCreate(
            ['player_id' => $player->id, 'reputation_id' => $reputation->id],
            ['points' => 0]
        );
    }

    public function getCurrentTier(Reputation $reputation, int $points): ?ReputationTier
    {
        return $reputation->tiers
            ->filter(fn ($t) => $points >= $t->min_points)
            ->sortByDesc('min_points')
            ->first();
    }

    public function canTakeQuest(Player $player, Reputation $reputation): bool
    {
        $hasActive = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->whereHas('quest', fn ($q) => $q->where('type', 'reputation'))
            ->whereHas('quest.reputationTierQuests.tier', fn ($q) => $q->where('reputation_id', $reputation->id))
            ->exists();

        if ($hasActive) {
            return false;
        }

        $pr = PlayerReputation::where('player_id', $player->id)
            ->where('reputation_id', $reputation->id)
            ->first();

        if (! $pr || ! $pr->last_completed_at) {
            return true;
        }

        return $pr->last_completed_at->addDays(2)->isPast();
    }

    public function getActiveQuest(Player $player, Reputation $reputation): ?QuestPlayer
    {
        return QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->whereHas('quest.reputationTierQuests.tier', fn ($q) => $q->where('reputation_id', $reputation->id))
            ->with('quest.objectives', 'objectives.questObjective')
            ->first();
    }

    public function assignQuest(Player $player, Reputation $reputation): ?QuestPlayer
    {
        $pr = $this->getOrCreate($player, $reputation);
        $tier = $this->getCurrentTier($reputation, $pr->points);

        if (! $tier) {
            return null;
        }

        $tierQuests = $tier->quests()->with('quest.objectives')->get();
        if ($tierQuests->isEmpty()) {
            return null;
        }

        $randomTierQuest = $tierQuests->random();
        $quest = $randomTierQuest->quest;

        return DB::transaction(function () use ($player, $quest) {
            $questPlayer = QuestPlayer::create([
                'player_id' => $player->id,
                'quest_id' => $quest->id,
                'status' => QuestPlayerStatus::IN_PROGRESS,
                'current_stage_id' => $quest->firstStage()?->id,
            ]);

            foreach ($quest->objectives as $objective) {
                QuestPlayerObjective::create([
                    'quest_player_id' => $questPlayer->id,
                    'quest_objective_id' => $objective->id,
                ]);
            }

            return $questPlayer;
        });
    }

    public function addPoints(Player $player, Reputation $reputation, int $amount): PlayerReputation
    {
        $pr = $this->getOrCreate($player, $reputation);
        $pr->increment('points', $amount);
        $pr->update(['last_completed_at' => now()]);
        $pr->refresh();

        return $pr;
    }

    public function getCooldownDiff(Player $player, Reputation $reputation): ?string
    {
        $pr = PlayerReputation::where('player_id', $player->id)
            ->where('reputation_id', $reputation->id)
            ->first();

        if (! $pr || ! $pr->last_completed_at) {
            return null;
        }

        $availableAt = $pr->last_completed_at->addDays(2);
        if ($availableAt->isPast()) {
            return null;
        }

        return $availableAt->locale('ru')->diffForHumans(now(), true, false, 2);
    }

    public function getEarnedMedals(Reputation $reputation, int $points): Collection
    {
        return $reputation->tiers
            ->filter(fn ($t) => $t->medal_name && $points >= $t->min_points)
            ->sortBy('min_points')
            ->values();
    }
}
