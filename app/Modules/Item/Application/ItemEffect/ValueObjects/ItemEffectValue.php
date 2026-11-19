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
        public ?int $targetShareItemId = null,
        public ?int $reputationId = null,
    ) {}

    public function withValue(int $value): self
    {
        return new self($this->type, $this->valueType, $value, $this->durationSeconds, $this->targetShareItemId, $this->reputationId);
    }

    public function isPercent(): bool
    {
        return $this->valueType === ItemEffectValueType::PERCENT;
    }

    public function isFlat(): bool
    {
        return $this->valueType === ItemEffectValueType::FLAT;
    }
}
