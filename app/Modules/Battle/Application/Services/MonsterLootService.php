<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services;

use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class MonsterLootService
{
    public function damage(MonsterOnLocation $monster, int $damage, ?int $userId, ?int $battleId = null, int $minimumHp = 0): void
    {
        $actualDamage = min(max(0, $monster->hp_now - $minimumHp), max(0, $damage));
        $monster->hp_now -= $actualDamage;
        if ($actualDamage === 0 || $userId === null || $monster->isClanBoss()) {
            return;
        }

        $battleId ??= BattleDetail::query()
            ->where('location_monster_id', $monster->id)
            ->whereHas('battle', fn ($query) => $query->where('status', BattleStatus::ACTIVE))
            ->latest('id')->value('battle_id');
        if ($battleId === null) {
            return;
        }

        DB::table('battle_monster_damage')->insert([
            'battle_id' => $battleId,
            'location_monster_id' => $monster->id,
            'user_id' => $userId,
            'damage' => $actualDamage,
        ]);
    }

    public function recipient(int $battleId, int $monsterId, User $fallback): User
    {
        // При равном уроне побеждает раньше достигший итоговой суммы.
        $userId = DB::table('battle_monster_damage')
            ->where('battle_id', $battleId)
            ->where('location_monster_id', $monsterId)
            ->select('user_id')->groupBy('user_id')
            ->orderByRaw('SUM(damage) DESC')->orderByRaw('MAX(id) ASC')
            ->value('user_id');

        return $userId === null || (int) $userId === (int) $fallback->id
            ? $fallback
            : (User::without('player')->find($userId) ?? $fallback);
    }
}
