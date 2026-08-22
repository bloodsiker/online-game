<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Services;

use App\Modules\Item\Domain\Enums\ItemActionType;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemActionLog;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class ItemActionLogger
{
    public function log(
        User $user,
        ShareItem $shareItem,
        int $upgradeLvl,
        ItemActionType $action,
        int $count,
        ?int $money = null,
        ?User $targetUser = null,
    ): void {
        ItemActionLog::create([
            'user_id' => $user->id,
            'share_item_id' => $shareItem->id,
            'item_name' => $shareItem->name,
            'upgrade_lvl' => $upgradeLvl,
            'action' => $action,
            'count' => $count,
            'money' => $money,
            'target_user_id' => $targetUser?->id,
        ]);
    }
}
