<?php

declare(strict_types=1);

namespace App\Modules\Npc\Infrastructure\Persistence;

use App\Modules\Npc\Domain\Contracts\NpcReadRepository;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueNode;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Domain\Enums\QuestType;
use App\Modules\Quest\Domain\Services\QuestDefinitionsCache;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use Illuminate\Support\Collection;

class EloquentNpcReadRepository implements NpcReadRepository
{
    public function findNpcByIdOrFail(int $id): Npc
    {
        return Npc::with(['structures.actions', 'location'])->findOrFail($id);
    }

    public function findNpcByUuidOrFail(string $uuid): Npc
    {
        return Npc::with(['structures.actions', 'location'])->where('uuid', $uuid)->firstOrFail();
    }

    public function findNpcByNameOrFail(string $name): Npc
    {
        return Npc::query()->where('name', $name)->firstOrFail();
    }

    public function getPlayerQuestStateGroups(int $playerId): array
    {
        $rows = QuestPlayer::where('player_id', $playerId)
            ->with('quest:id,type')
            ->get(['id', 'quest_id', 'status', 'reset_at']);

        $completed = [];
        $inProgress = [];
        $repeatableReady = [];

        foreach ($rows as $row) {
            if ($row->status === QuestPlayerStatus::IN_PROGRESS) {
                $inProgress[] = $row->quest_id;

                continue;
            }

            if ($row->status !== QuestPlayerStatus::COMPLETED) {
                continue;
            }

            $completed[] = $row->quest_id;

            if ($row->quest?->type === QuestType::REPEATABLE
                && ($row->reset_at === null || $row->reset_at->lte(now()))
            ) {
                $repeatableReady[] = $row->quest_id;
            }
        }

        return [
            'completed' => $completed,
            'in_progress' => $inProgress,
            'repeatable_ready' => $repeatableReady,
        ];
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
        // Статичный список квестов НПС — из версионного кэша; динамические
        // условия игрока (исключения, цепочки, клан) применяются в памяти.
        return QuestDefinitionsCache::availableByNpc($npcId)
            ->reject(fn (Quest $quest): bool => in_array((int) $quest->id, $excludeIds, true))
            ->filter(fn (Quest $quest): bool => $quest->after_quest_id === null
                || in_array((int) $quest->after_quest_id, $completedQuestIds, true))
            ->filter(fn (Quest $quest): bool => $hasClanMembership || $quest->type !== QuestType::CLAN)
            ->values();
    }

    public function getNpcReputations(int $npcId): Collection
    {
        return Reputation::with('tiers.quests.quest', 'shopItems')
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

    public function getStartDialogueNode(int $npcId): ?NpcDialogueNode
    {
        return NpcDialogueNode::where('npc_id', $npcId)
            ->where('is_active', true)
            ->orderByDesc('is_start')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }
}
