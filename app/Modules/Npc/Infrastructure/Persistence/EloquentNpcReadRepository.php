<?php

declare(strict_types=1);

namespace App\Modules\Npc\Infrastructure\Persistence;

use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Npc\Domain\Contracts\NpcReadRepository;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use Illuminate\Support\Collection;

class EloquentNpcReadRepository implements NpcReadRepository
{
    public function findNpcOrFail(int $id): Npc
    {
        return Npc::with('structures.actions')->findOrFail($id);
    }

    public function getCompletedQuestIds(int $playerId): array
    {
        return QuestPlayer::where('player_id', $playerId)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->pluck('quest_id')
            ->toArray();
    }

    public function getInProgressQuestIds(int $playerId): array
    {
        return QuestPlayer::where('player_id', $playerId)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->pluck('quest_id')
            ->toArray();
    }

    public function getRepeatableReadyIds(int $playerId): array
    {
        return QuestPlayer::where('player_id', $playerId)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->whereHas('quest', fn ($q) => $q->where('type', 'repeatable'))
            ->where(fn ($q) => $q->whereNull('reset_at')->orWhere('reset_at', '<=', now()))
            ->pluck('quest_id')
            ->toArray();
    }

    public function getQuestsOnCooldown(int $playerId, int $npcId): Collection
    {
        return QuestPlayer::where('player_id', $playerId)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->whereHas('quest', fn ($q) => $q->where('type', 'repeatable')
                ->where('start_npc_id', $npcId)
                ->isActive()
            )
            ->where('reset_at', '>', now())
            ->with('quest')
            ->get()
            ->map(fn ($qp) => (object) [
                'quest' => $qp->quest,
                'reset_at' => $qp->reset_at,
                'diff' => $qp->reset_at->locale('ru')->diffForHumans(now(), true, false, 2),
            ]);
    }

    public function getClanProgressAtNpc(int $clanId, int $npcId): Collection
    {
        return QuestClanProgress::where('clan_id', $clanId)
            ->whereHas('quest', fn ($q) => $q->where('start_npc_id', $npcId)->isActive())
            ->with('quest')
            ->get();
    }

    public function getClanInProgressForUser(int $userId, int $clanId): Collection
    {
        return QuestClanProgress::where('user_id', $userId)
            ->where('clan_id', $clanId)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('quest', 'currentStage.objectives')
            ->get();
    }

    public function getAvailableQuests(int $npcId, array $excludeIds, array $completedQuestIds, bool $hasClanMembership): Collection
    {
        return Quest::whereNotIn('id', $excludeIds)
            ->isActive()
            ->where('start_npc_id', $npcId)
            ->where('type', '!=', 'reputation')
            ->where(function ($query) use ($completedQuestIds) {
                $query->whereNull('after_quest_id')
                    ->orWhereIn('after_quest_id', $completedQuestIds);
            })
            ->when(! $hasClanMembership, fn ($q) => $q->where('type', '!=', 'clan'))
            ->get();
    }

    public function getNpcReputations(int $npcId): Collection
    {
        return Reputation::with('tiers.quests.quest')
            ->where('npc_id', $npcId)
            ->get();
    }

    public function getInProgressQuestPlayers(int $playerId, array $questIds): Collection
    {
        return QuestPlayer::where('player_id', $playerId)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->whereIn('quest_id', $questIds)
            ->with('objectives.questObjective', 'quest', 'currentStage.objectives')
            ->get();
    }
}
