<?php

declare(strict_types=1);

namespace App\Modules\Npc\Domain\Contracts;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use Illuminate\Support\Collection;

interface NpcReadRepository
{
    public function findNpcOrFail(int $id): Npc;

    public function getCompletedQuestIds(int $playerId): array;

    public function getInProgressQuestIds(int $playerId): array;

    public function getRepeatableReadyIds(int $playerId): array;

    public function getQuestsOnCooldown(int $playerId, int $npcId): Collection;

    public function getClanProgressAtNpc(int $clanId, int $npcId): Collection;

    public function getClanInProgressForUser(int $userId, int $clanId): Collection;

    public function getAvailableQuests(int $npcId, array $excludeIds, array $completedQuestIds, bool $hasClanMembership): Collection;

    public function getNpcReputations(int $npcId): Collection;

    public function getInProgressQuestPlayers(int $playerId, array $questIds): Collection;
}
