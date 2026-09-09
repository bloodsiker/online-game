<?php

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\Services\DropService;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;

readonly class BattleFinishService
{
    public function __construct(
        private BattleRepository $battleRepository,
        private DropService $dropService,
    ) {}

    public function checkAndFinish(Battle $battle, Location $location, User $dropRecipient): object
    {
        if ($battle->status->isFinish()) {
            return (object) ['battle' => $battle];
        }

        $active = BattleDetail::where('battle_id', $battle->id)
            ->whereHas('locationMonster', fn ($q) => $q->where('active', 1))
            ->exists();

        if ($active) {
            return (object) ['battle' => $battle];
        }

        $battle->rounds++;
        $battle->save();

        $battle = $this->battleRepository->finishBattle($battle->id);

        $dungeonSessionId = $battle->detailsWithMonsters->first()?->locationMonster?->dungeon_session_id;
        $this->dropService->dropItemsFromMonsters($battle, $location, $dropRecipient, $dungeonSessionId);

        $location->last_respawn_monster_at = Carbon::now();
        $location->save();

        foreach ($battle->detailsWithUsers as $userInBattle) {
            if ($userInBattle->status->isLife()) {
                $userInBattle->user->player->victory++;
                $userInBattle->user->player->save();
            }
        }

        return (object) ['battle' => $battle];
    }

    /**
     * Завершает бой без награды, когда в нём не осталось живых игроков.
     * Монстры при этом остаются активными на локации и могут начать новый бой.
     */
    public function finishIfNoLivingPlayers(Battle $battle): bool
    {
        $hasLivingPlayers = BattleDetail::query()
            ->where('battle_id', $battle->id)
            ->whereNotNull('user_id')
            ->where('status', BattleDetailStatus::LIFE)
            ->exists();

        if ($hasLivingPlayers) {
            return false;
        }

        $battle->status = BattleStatus::FINISH;
        $battle->save();

        return true;
    }
}
