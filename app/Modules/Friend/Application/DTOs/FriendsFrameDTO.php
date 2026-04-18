<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\DTOs;

final readonly class FriendsFrameDTO
{
    /**
     * @param  list<FriendEntryDTO>  $friends
     */
    public function __construct(public array $friends) {}
}
