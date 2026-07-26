<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class ItemInfoPageDTO
{
    /**
     * @param  list<array{title: string, value: string}>  $stats
     * @param  list<array{label: string, value: int, met: bool}>  $requirements
     * @param  list<array{title: string, value: string}>  $gems
     * @param  list<array{title: string, value: string}>  $runes
     */
    public function __construct(
        public int $itemId,
        public int $shareItemId,
        public string $name,
        public string $color,
        public ?string $image,
        public string $typeName,
        public int $price,
        public ?string $description,
        public bool $noGive,
        public bool $noWeight,
        public bool $noSell,
        public ?string $gateLocations,
        public array $stats,
        public array $requirements,
        public string $handOverUrl,
        public string $dropUrl,
        public string $sameItemsUrl,
        public string $backpackUrl,
        public array $gems = [],
        public array $runes = [],
    ) {}
}
