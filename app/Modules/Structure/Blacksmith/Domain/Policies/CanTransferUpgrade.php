<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Policies;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;

final readonly class CanTransferUpgrade
{
    public function __construct(private CanUpgradeItem $canUpgradeItem) {}

    public function check(ShareItem $source, ShareItem $target): bool
    {
        return $this->canUpgradeItem->check($source)
            && $this->canUpgradeItem->check($target)
            && $this->compatibilityKey($source) !== null
            && $this->compatibilityKey($source) === $this->compatibilityKey($target);
    }

    public function compatibilityKey(ShareItem $item): ?string
    {
        if (! $this->canUpgradeItem->check($item)) {
            return null;
        }

        if ($item->type === ShareItemType::ARMOR) {
            return $item->slot === null ? null : $item->type->value.':'.$item->slot->value;
        }

        return $item->type->value;
    }
}
