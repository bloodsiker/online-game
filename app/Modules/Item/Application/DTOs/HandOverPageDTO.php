<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class HandOverPageDTO
{
    /**
     * @param  list<HandOverCandidateDTO>  $candidates
     */
    public function __construct(
        public int $itemId,
        public string $itemName,
        public bool $isHandedItem,
        public bool $isUserMoved,
        public ?string $toUserName,
        public array $candidates,
        public string $backpackUrl,
        public string $locationUrl,
        public string $sameItemsUrl,
    ) {}
}
