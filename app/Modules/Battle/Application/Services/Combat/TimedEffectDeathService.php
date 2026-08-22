<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

final readonly class TimedEffectDeathService
{
    public function __construct(
        private PlayerDeathFinalizer $deathFinalizer,
    ) {}

    /**
     * Finalizes a death caused by poison/burning between combat actions and
     * persists it in the active battle log when the player is in a battle.
     */
    public function handle(Player $player, string $effectName): bool
    {
        $player->loadMissing('user');
        $user = $player->user;

        $participant = $user === null
            ? null
            : BattleDetail::query()
                ->where('user_id', $user->id)
                ->whereIn('battle_id', Battle::query()
                    ->select('id')
                    ->where('status', BattleStatus::ACTIVE))
                ->orderByDesc('battle_id')
                ->lockForUpdate()
                ->first();

        $battle = $participant === null
            ? null
            : Battle::query()
                ->whereKey($participant->battle_id)
                ->where('status', BattleStatus::ACTIVE)
                ->lockForUpdate()
                ->first();

        $escapedEffectName = htmlspecialchars($effectName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $result = (new AttackResultDTO)->log(
            '<p><font color="red"><b>'.$escapedEffectName.'</b></font> наносит смертельный урон.</p>',
        );

        if (! $this->deathFinalizer->finalize($player, $participant, $result)) {
            return false;
        }

        if ($battle !== null && $user !== null) {
            $monster = BattleDetail::query()
                ->where('battle_id', $battle->id)
                ->whereNotNull('location_monster_id')
                ->orderByDesc('status')
                ->first();

            $battle->rounds = (int) $battle->rounds + 1;
            $battle->save();

            BattleRound::query()->create([
                'battle_id' => $battle->id,
                'round_number' => $battle->rounds,
                'user_id' => $user->id,
                'location_monster_id' => $monster?->location_monster_id,
                'action' => $result->getLog(),
            ]);
        }

        return true;
    }
}
