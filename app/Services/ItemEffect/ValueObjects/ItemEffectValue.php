<?php

namespace App\Services\ItemEffect\ValueObjects;

use App\Enums\ItemEffectType;
use App\Enums\ItemEffectValueType;

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
