<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\DTOs;

final readonly class FriendEntryDTO
{
    public function __construct(
        public int $relationshipId,
        public int $userId,
        public string $userName,
        public int $level,
        public bool $isOnline,
        public ?string $lastOnlineLabel,
        public ?string $lastOnlineTime,
        public ?string $clanName,
        public ?string $clanIconUrl,
    ) {}
}
