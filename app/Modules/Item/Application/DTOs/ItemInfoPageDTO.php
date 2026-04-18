<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class ItemInfoPageDTO
{
    public function __construct(
        public int $itemId,
        public int $shareItemId,
        public string $name,
        public ?string $image,
        public ?int $minAttack,
        public ?int $maxAttack,
        public bool $isHandSlot,
        public bool $isTwoHand,
        public ?string $skillName,
        public ?int $skillLevel,
        public string $typeName,
        public string $handOverUrl,
        public string $dropUrl,
        public string $sameItemsUrl,
        public string $backpackUrl,
    ) {}
}
