<?php

namespace App\Modules\Item\Application\ItemEffect\ValueObjects;

use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;

final readonly class ItemEffectValue
{
    public function __construct(
        public ItemEffectType $type,
        public ItemEffectValueType $valueType,
        public int $value,
        public ?int $durationSeconds = null,
    ) {}

    public function isPercent(): bool
    {
        return $this->valueType === ItemEffectValueType::PERCENT;
    }

    public function isFlat(): bool
    {
        return $this->valueType === ItemEffectValueType::FLAT;
    }
}
