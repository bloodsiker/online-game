<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\Services;

use App\Modules\Dungeon\Domain\Enums\DungeonRunStatus;
use App\Modules\Dungeon\Domain\Enums\DungeonStageSpawnType;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStageMonster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final readonly class DungeonStageService
{
    public function __construct(private DungeonRewardService $rewardService) {}

    /** @param Collection<int, User> $users */
    public function start(DungeonRun $run, DungeonSession $primarySession, Collection $users): void
    {
        $stage = DungeonStage::query()
            ->with(['locations', 'monsters.monster'])
            ->where('dungeon_id', $run->dungeon_id)
            ->orderBy('number')
            ->first();

        if ($stage === null || $stage->entryLocation() === null) {
            throw new RuntimeException('Для башни не настроен первый этаж или его входная локация.');
        }

        $this->activate($run, $stage, $primarySession->monsterSessionId(), $users);
    }

    public function handleMonsterKilled(MonsterOnLocation $monster): ?string
    {
        if ($monster->dungeon_session_id === null) {
            return null;
        }

        $session = DungeonSession::query()
            ->with(['run.dungeon', 'run.currentStage.locations'])
            ->whereKey($monster->dungeon_session_id)
            ->first();

        if ($session?->run === null || ! $session->run->dungeon->isTower()) {
            return null;
        }

        return DB::transaction(function () use ($session): ?string {
            $run = DungeonRun::query()->whereKey($session->run->id)->lockForUpdate()->firstOrFail();
            if ($run->status !== DungeonRunStatus::ACTIVE || $run->current_stage_id === null) {
                return null;
            }

            $stage = DungeonStage::query()->with('locations')->findOrFail($run->current_stage_id);
            $locationIds = $stage->locations->modelKeys();
            $hasMonsters = MonsterOnLocation::query()
                ->where('dungeon_session_id', $session->monsterSessionId())
                ->whereIn('location_id', $locationIds)
                ->where('active', true)
                ->exists();

            if ($hasMonsters) {
                return null;
            }

            $next = $stage->next_stage_id !== null
                ? DungeonStage::query()->with(['locations', 'monsters.monster'])->find($stage->next_stage_id)
                : DungeonStage::query()->with(['locations', 'monsters.monster'])
                    ->where('dungeon_id', $run->dungeon_id)
                    ->where('number', '>', $stage->number)
                    ->orderBy('number')
                    ->first();

            $users = $this->participantUsers($run);
            if ($next !== null) {
                $this->activate($run, $next, $session->monsterSessionId(), $users);

                return sprintf('Этаж %d очищен. Вы поднялись на этаж %d.', $stage->number, $next->number);
            }

            $run->update([
                'status' => DungeonRunStatus::COMPLETED,
                'finished_at' => now(),
                'stage_expires_at' => null,
            ]);
            $run->participants()->update(['status' => 'completed', 'finished_at' => now()]);
            $run->sessions()->update(['completed_at' => now()]);
            foreach ($users as $user) {
                $this->rewardService->grant($run->dungeon, $user);
            }

            return 'Башня полностью пройдена. Награда выдана.';
        });
    }

    private function activate(DungeonRun $run, DungeonStage $stage, int $monsterSessionId, Collection $users): void
    {
        $entry = $stage->entryLocation();
        if ($entry === null) {
            throw new RuntimeException("Для этажа {$stage->number} не настроена входная локация.");
        }

        $run->update([
            'current_stage_id' => $stage->id,
            'stage_started_at' => now(),
            'stage_expires_at' => $stage->time_limit_seconds !== null ? now()->addSeconds($stage->time_limit_seconds) : null,
        ]);

        $userIds = $users->pluck('id')->all();
        $oldLocationIds = User::query()->whereIn('id', $userIds)->pluck('location_id')->filter()->unique();
        User::query()->whereIn('id', $userIds)->update([
            'prev_location_id' => DB::raw('location_id'),
            'location_id' => $entry->id,
        ]);
        foreach ($oldLocationIds->push($entry->id)->unique() as $locationId) {
            Cache::forget('who:users_on_location:'.$locationId);
            Cache::forget('who:users_on_location:'.$locationId.':run:'.$run->id);
        }

        $this->spawn($stage, $monsterSessionId);
    }

    private function spawn(DungeonStage $stage, int $monsterSessionId): void
    {
        $stage->loadMissing(['locations', 'monsters.monster']);
        $locations = $stage->locations->values();
        if ($locations->isEmpty()) {
            throw new RuntimeException("У этажа {$stage->number} нет локаций.");
        }

        $definitions = $stage->monsters->filter(fn (DungeonStageMonster $definition): bool => $definition->monster !== null)->values();
        if ($definitions->isEmpty()) {
            throw new RuntimeException("У этажа {$stage->number} не настроены монстры.");
        }

        $spawnRows = [];
        if ($stage->spawn_type === DungeonStageSpawnType::FIXED) {
            foreach ($definitions as $definition) {
                $locationId = $definition->location_id ?? $stage->entryLocation()?->id;
                for ($i = 0; $i < $definition->quantity; $i++) {
                    $spawnRows[] = [$locationId, $definition];
                }
            }
        } else {
            $total = max(1, (int) $stage->total_monsters);
            $weighted = $definitions->flatMap(
                fn (DungeonStageMonster $definition) => array_fill(0, max(1, $definition->weight), $definition)
            )->values();
            for ($i = 0; $i < $total; $i++) {
                $spawnRows[] = [$locations[$i % $locations->count()]->id, $weighted->random()];
            }
            shuffle($spawnRows);
        }

        foreach ($spawnRows as [$locationId, $definition]) {
            MonsterOnLocation::query()->create([
                'location_id' => $locationId,
                'dungeon_session_id' => $monsterSessionId,
                'monster_id' => $definition->monster_id,
                'hp_now' => $definition->monster->hp,
                'hp_max' => $definition->monster->hp,
                'active' => true,
                'aggression' => $definition->monster->aggression,
            ]);
        }
    }

    /** @return Collection<int, User> */
    private function participantUsers(DungeonRun $run): Collection
    {
        return User::query()->without('player')
            ->whereIn('id', $run->participants()->where('status', 'active')->pluck('user_id'))
            ->get();
    }
}
