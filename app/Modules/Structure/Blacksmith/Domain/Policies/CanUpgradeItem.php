<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Policies;

use App\Enums\ShareItemType;
use App\Models\Share\ShareItem;

final class CanUpgradeItem
{
    public function check(ShareItem $item): bool
    {
        return in_array($item->type, [
            ShareItemType::WEAPON,
            ShareItemType::SHIELD,
            ShareItemType::ARMOR,
            ShareItemType::BELT,
        ], true);
    }
}
