<?php

declare(strict_types=1);

namespace App\Modules\Influence\Domain\Services;

use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluence;
use Illuminate\Support\Facades\DB;

final class MapInfluenceRequirementService
{
    /** @var array<string, int> */
    private array $points = [];

    /** @var array<string, bool> */
    private array $decisions = [];

    public function has(int $userId, int $mapId, int $requiredInfluence): bool
    {
        if ($requiredInfluence <= 0) {
            return true;
        }

        $key = $userId.':'.$mapId;
        $points = $this->points[$key] ??= (int) MapInfluence::query()
            ->where('user_id', $userId)
            ->where('map_id', $mapId)
            ->value('influence');

        return $points >= $requiredInfluence;
    }

    public function allowsNpc(int $userId, int $npcId): bool
    {
        return $this->allowsTarget($userId, 'npc_influence_requirements', 'npc_id', $npcId);
    }

    public function allowsQuest(int $userId, int $questId): bool
    {
        return $this->allowsTarget($userId, 'quest_influence_requirements', 'quest_id', $questId);
    }

    public function allowsLocationGate(int $userId, int $gateId): bool
    {
        return $this->allowsTarget($userId, 'location_gate_influence_requirements', 'location_gate_id', $gateId);
    }

    public function forget(int $userId, int $mapId): void
    {
        unset($this->points[$userId.':'.$mapId]);
        $this->decisions = [];
    }

    private function allowsTarget(int $userId, string $table, string $targetColumn, int $targetId): bool
    {
        $decisionKey = $userId.':'.$table.':'.$targetId;
        if (array_key_exists($decisionKey, $this->decisions)) {
            return $this->decisions[$decisionKey];
        }

        $requirements = DB::table($table)->where($targetColumn, $targetId)->get();

        foreach ($requirements as $requirement) {
            if (! $this->has($userId, (int) $requirement->map_id, (int) $requirement->required_influence)) {
                return $this->decisions[$decisionKey] = false;
            }
        }

        return $this->decisions[$decisionKey] = true;
    }
}
