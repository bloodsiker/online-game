<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

use App\Models\Share\ShareItem;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanBreakItem;
use App\Modules\Structure\Blacksmith\Domain\Results\SalvageResult;

final class BreakService
{
    public function __construct(
        private readonly CanBreakItem $canBreakItem,
    ) {}

    public function salvage(ShareItem $item): SalvageResult
    {
        if (! $this->canBreakItem->check($item)) {
            return SalvageResult::itemNotBreakable();
        }

        return SalvageResult::success($item->break_crystal);
    }
}
