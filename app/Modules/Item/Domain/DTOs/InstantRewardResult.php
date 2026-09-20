<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\DTOs;

use App\Modules\Item\Domain\Enums\InstantRewardType;

final readonly class InstantRewardResult
{
    public function __construct(
        public InstantRewardType $type,
        public int $amount,
        public int $shareItemId,
        public string $name,
        public string $image,
    ) {}

    /** @return array{share_item_id: int, name: string, image: string, count: int, reward_type: string} */
    public function toLootArray(): array
    {
        return [
            'share_item_id' => $this->shareItemId,
            'name' => $this->name,
            'image' => $this->image,
            'count' => $this->amount,
            'reward_type' => $this->type->value,
        ];
    }
}
