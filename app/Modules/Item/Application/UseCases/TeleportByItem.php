<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Item\Domain\Exceptions\ItemUseBlockedException;
use App\Modules\Item\Domain\Services\ItemUsagePolicyService;
use App\Modules\Item\Infrastructure\Persistence\Models\LockpickingAttempt;
use App\Modules\Location\Infrastructure\Persistence\Models\GatheringAttempt;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Prison\Application\Services\DetentionService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Многоразовый предмет-телепорт (эффект TELEPORT_LOCATION, value — id локации):
 * переносит игрока на заданную локацию. Нельзя использовать в бою, в подземелье
 * и в Темнице; частоту ограничивает лимит использования предмета (share_item_use_limits).
 * Вызывается внутри транзакции использования предмета.
 */
final readonly class TeleportByItem
{
    public function __construct(
        private BattleRepository $battleRepository,
        private DetentionService $detentionService,
        private ItemUsagePolicyService $itemUsagePolicyService,
    ) {}

    /** @throws ItemUseBlockedException */
    public function execute(User $user, Player $player, ShareItem $item, int $locationId): void
    {
        $location = Location::query()->find($locationId);
        if ($location === null) {
            throw new ItemUseBlockedException(__('Предмет настроен неверно. Обратитесь к администрации.'), 'invalid_teleport');
        }

        if ((int) $user->location_id === $locationId) {
            throw new ItemUseBlockedException(__('Вы уже находитесь в этом месте.'), 'teleport_same_location');
        }

        if ($detentionMessage = $this->detentionService->blockLeaving($user)) {
            throw new ItemUseBlockedException($detentionMessage, 'teleport_detention');
        }

        if ($this->battleRepository->hasLivingPlayerInActiveBattle((int) $user->id, (int) $user->location_id)) {
            throw new ItemUseBlockedException(__('Нельзя телепортироваться во время боя.'), 'teleport_in_battle');
        }

        if (DungeonSession::query()->where('user_id', $user->id)->exists()) {
            throw new ItemUseBlockedException(__('Руна не работает в подземельях.'), 'teleport_in_dungeon');
        }

        $this->itemUsagePolicyService->reserveUse($player, $item);

        $oldLocationId = (int) $user->location_id;

        GatheringAttempt::query()->where('player_id', $player->id)->delete();
        LockpickingAttempt::query()->where('player_id', $player->id)->delete();

        $user->prev_location_id = $oldLocationId;
        $user->location_id = $locationId;
        $user->save();

        Cache::forget('who:users_on_location:'.$oldLocationId);
        Cache::forget('who:users_on_location:'.$locationId);
    }
}
