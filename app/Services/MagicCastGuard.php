<?php

declare(strict_types=1);

namespace App\Services;

use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\DB;

/**
 * Единая точка списания маны + установки кулдауна для обоих путей применения
 * заклинания (боевой каст и внебоевой баф/хил) — под блокировкой строки
 * player_magic_skills, чтобы два параллельных запроса не списали ману дважды
 * и не обошли кулдаун одновременно (см. спеку, раздел «Общий механизм каста»).
 */
class MagicCastGuard
{
    public function tryConsume(Player $player, MagicSkill $skill): CastAttemptResult
    {
        return DB::transaction(function () use ($player, $skill): CastAttemptResult {
            $pivot = DB::table('player_magic_skills')
                ->where('player_id', $player->id)
                ->where('magic_skill_id', $skill->id)
                ->lockForUpdate()
                ->first();

            if ($pivot === null) {
                return CastAttemptResult::failure('Заклинание не изучено');
            }

            if ($pivot->cooldown_end_at !== null && now()->lt($pivot->cooldown_end_at)) {
                $remaining = (int) now()->diffInSeconds($pivot->cooldown_end_at, false);

                return CastAttemptResult::failure(sprintf('Перезарядка ещё %d сек.', $remaining));
            }

            $freshPlayer = Player::whereKey($player->id)->lockForUpdate()->first();

            if ($freshPlayer === null || $freshPlayer->mp_now < $skill->mana_cost) {
                return CastAttemptResult::failure(sprintf('Недостаточно маны, требуется %s', $skill->mana_cost));
            }

            $freshPlayer->mp_now -= $skill->mana_cost;
            $freshPlayer->save();
            $player->mp_now = $freshPlayer->mp_now;

            $cooldownEndAt = $skill->cooldown > 0 ? now()->addSeconds($skill->cooldown) : null;

            DB::table('player_magic_skills')
                ->where('player_id', $player->id)
                ->where('magic_skill_id', $skill->id)
                ->update(['cooldown_end_at' => $cooldownEndAt]);

            return CastAttemptResult::success();
        });
    }
}
