<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\Services;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
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
            // Строка на пару (игрок, квест) единственная (UNIQUE):
            // повторная выдача ежедневки переиспользует её, как это делает
            // QuestController::take() для repeatable-квестов.
            $questPlayer = QuestPlayer::query()
                ->where('player_id', $player->id)
                ->where('quest_id', $quest->id)
                ->lockForUpdate()
                ->first();

            if ($questPlayer) {
                $questPlayer->objectives()->delete();
                $questPlayer->update([
                    'status' => QuestPlayerStatus::IN_PROGRESS,
                    'current_stage_id' => $quest->firstStage()?->id,
                    'completed_at' => null,
                    'reset_at' => null,
                ]);
            } else {
                $questPlayer = QuestPlayer::create([
                    'player_id' => $player->id,
                    'quest_id' => $quest->id,
                    'status' => QuestPlayerStatus::IN_PROGRESS,
                    'current_stage_id' => $quest->firstStage()?->id,
                ]);
            }

            foreach ($quest->objectives as $objective) {
                QuestPlayerObjective::create([
                    'quest_player_id' => $questPlayer->id,
                    'quest_objective_id' => $objective->id,
                ]);
            }

            return $questPlayer;
        });
    }

    public function addPoints(Player $player, Reputation $reputation, int $amount, bool $touchCooldown = true): PlayerReputation
    {
        $pr = $this->getOrCreate($player, $reputation);
        $pr->increment('points', $amount);
        if ($touchCooldown) {
            $pr->update(['last_completed_at' => now()]);
        }
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

    /**
     * Медаль тира получена, если набраны очки тира И (если у тира задан
     * подвиг) выполнен квест-подвиг (feat_quest_id — финальный квест цепочки).
     */
    public function getEarnedMedals(Reputation $reputation, int $points, Player $player): Collection
    {
        $eligibleTiers = $reputation->tiers
            ->filter(fn ($tier) => $tier->medal_name && $points >= $tier->min_points);

        $completedFeatQuestIds = $this->completedQuestIds(
            $player,
            $eligibleTiers->pluck('feat_quest_id')->filter()
        );

        return $eligibleTiers
            ->filter(fn ($tier) => ! $tier->feat_quest_id || $completedFeatQuestIds->has($tier->feat_quest_id))
            ->sortBy('min_points')
            ->values();
    }

    public function isFeatCompleted(Player $player, ReputationTier $tier): bool
    {
        if (! $tier->feat_quest_id) {
            return true;
        }

        return QuestPlayer::where('player_id', $player->id)
            ->where('quest_id', $tier->feat_quest_id)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->exists();
    }

    /**
     * Следующий доступный квест-подвиг для игрока по этой репутации.
     * Берётся самый ранний тир, где очки набраны, а подвиг ещё не выполнен.
     * Для цепочки идём от финального квеста назад по after_quest_id и
     * предлагаем первый невыполненный; если он уже взят — не предлагаем.
     */
    public function getAvailableFeatQuest(Player $player, Reputation $reputation, int $points): ?Quest
    {
        $eligibleTiers = $reputation->tiers
            ->filter(fn ($tier) => $tier->feat_quest_id && $points >= $tier->min_points);

        $completedFeatQuestIds = $this->completedQuestIds(
            $player,
            $eligibleTiers->pluck('feat_quest_id')
        );

        $tier = $eligibleTiers
            ->filter(fn ($tier) => ! $completedFeatQuestIds->has($tier->feat_quest_id))
            ->sortBy('min_points')
            ->first();

        if (! $tier) {
            return null;
        }

        // Разворачиваем цепочку: финальный квест -> ... -> первый
        $chain = [];
        $quest = $tier->featQuest;
        while ($quest) {
            array_unshift($chain, $quest);
            $quest = $quest->afterQuest;
        }

        $questProgress = QuestPlayer::where('player_id', $player->id)
            ->whereIn('quest_id', collect($chain)->pluck('id'))
            ->without(['objectives.questObjective', 'quest'])
            ->get()
            ->keyBy('quest_id');

        foreach ($chain as $quest) {
            $qp = $questProgress->get($quest->id);

            if (! $qp) {
                return $quest; // ещё не брал — предлагаем
            }
            if ($qp->status !== QuestPlayerStatus::COMPLETED) {
                return null; // взят и в процессе — не предлагаем повторно
            }
        }

        return null;
    }

    private function completedQuestIds(Player $player, Collection $questIds): Collection
    {
        $questIds = $questIds->filter()->unique()->values();

        if ($questIds->isEmpty()) {
            return collect();
        }

        return QuestPlayer::where('player_id', $player->id)
            ->whereIn('quest_id', $questIds)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->pluck('quest_id')
            ->flip();
    }
}
