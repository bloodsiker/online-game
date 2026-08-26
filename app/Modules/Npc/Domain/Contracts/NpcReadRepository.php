<?php

declare(strict_types=1);

namespace App\Modules\Npc\Domain\Contracts;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueNode;
use Illuminate\Support\Collection;

interface NpcReadRepository
{
    public function findNpcByIdOrFail(int $id): Npc;

    public function findNpcByUuidOrFail(string $uuid): Npc;

    public function findNpcByNameOrFail(string $name): Npc;

    /**
     * Состояния всех квестов игрока одним запросом — вместо трёх отдельных
     * выборок completed / in_progress / repeatable_ready.
     *
     * @return array{completed: list<int>, in_progress: list<int>, repeatable_ready: list<int>}
     */
    public function getPlayerQuestStateGroups(int $playerId): array;

    public function getQuestsOnCooldown(int $playerId, int $npcId): Collection;

    public function getClanProgressAtNpc(int $clanId, int $npcId): Collection;

    public function getClanInProgressForUser(int $userId, int $clanId): Collection;

    public function getAvailableQuests(int $npcId, array $excludeIds, array $completedQuestIds, bool $hasClanMembership): Collection;

    public function getNpcReputations(int $npcId): Collection;

    public function getInProgressQuestPlayers(int $playerId, array $questIds): Collection;

    public function getStartDialogueNode(int $npcId): ?NpcDialogueNode;
}
