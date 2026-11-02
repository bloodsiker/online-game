<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\Services;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\MagicSkill\Application\DTOs\CastAttemptResult;
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
    public function tryConsume(Player $player, MagicSkill $skill, int $magicSkillLevel = 0): CastAttemptResult
    {
        return DB::transaction(function () use ($player, $skill, $magicSkillLevel): CastAttemptResult {
            $pivot = DB::table('player_magic_skills')
                ->where('player_id', $player->id)
                ->where('magic_skill_id', $skill->id)
                ->lockForUpdate()
                ->first();

            if ($pivot === null) {
                return CastAttemptResult::failure(__('Заклинание не изучено'));
            }

            if ($pivot->cooldown_end_at !== null && now()->lt($pivot->cooldown_end_at)) {
                $remaining = (int) now()->diffInSeconds($pivot->cooldown_end_at, false);

                return CastAttemptResult::failure(sprintf(__('Перезарядка ещё %d сек.'), $remaining));
            }

            $freshPlayer = Player::whereKey($player->id)->lockForUpdate()->first();

            // «Колдовство» снижает стоимость маны той же кривой, что усиливает урон/хил (см. MagicHitCalculator::magicPower) — симметрично оружейному навыку.
            $manaCost = (int) ceil($skill->mana_cost / HitCalculator::weaponSkillMultiplier($magicSkillLevel));

            if ($freshPlayer === null || $freshPlayer->mp_now < $manaCost) {
                return CastAttemptResult::failure(sprintf(__('Недостаточно маны, требуется %s'), $manaCost));
            }

            $freshPlayer->mp_now -= $manaCost;
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
