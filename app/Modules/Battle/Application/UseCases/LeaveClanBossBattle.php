<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\UseCases;

use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class LeaveClanBossBattle
{
    public function execute(User $user, int $battleId): int
    {
        return DB::transaction(function () use ($user, $battleId): int {
            $battle = Battle::query()->whereKey($battleId)->lockForUpdate()->firstOrFail();
            if (! $battle->status->isActive()) {
                throw new RuntimeException('Этот бой уже завершён.');
            }

            $participant = BattleDetail::query()
                ->where('battle_id', $battle->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();
            if ($participant === null) {
                throw new RuntimeException('Вы не участвуете в этом бою.');
            }

            $bossInstanceId = BattleDetail::query()
                ->where('battle_id', $battle->id)
                ->whereNotNull('location_monster_id')
                ->whereHas('locationMonster', static fn ($query) => $query->whereNotNull('clan_boss_stage_state_id'))
                ->value('location_monster_id');
            if ($bossInstanceId === null) {
                throw new RuntimeException('Безопасный выход доступен только в бою с клановым боссом.');
            }

            $boss = MonsterOnLocation::query()->findOrFail($bossInstanceId);
            $stageState = ClanBossRunStage::query()->findOrFail($boss->clan_boss_stage_state_id);
            $structure = Structure::query()
                ->where('dungeon_id', $stageState->run()->value('dungeon_id'))
                ->where('type', Structure::TYPE_CLAN_BOSS)
                ->orderBy('id')
                ->first();
            if ($structure === null) {
                throw new RuntimeException('Не удалось найти логово кланового босса.');
            }

            // Leave the battle without creating a round or touching the player's
            // per-run attack batch: unused hits remain available on re-entry.
            $participant->delete();

            $hasOtherParticipants = BattleDetail::query()
                ->where('battle_id', $battle->id)
                ->whereNotNull('user_id')
                ->exists();
            if (! $hasOtherParticipants) {
                $battle->update(['status' => BattleStatus::FINISH]);
            }

            return (int) $structure->id;
        });
    }
}
