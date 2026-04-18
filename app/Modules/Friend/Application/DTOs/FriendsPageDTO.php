<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\DTOs;

final readonly class FriendsPageDTO
{
    /**
     * @param  list<FriendEntryDTO>  $friends
     * @param  list<FriendEntryDTO>  $outgoing
     * @param  list<FriendEntryDTO>  $incoming
     * @param  list<FriendEntryDTO>  $enemies
     * @param  list<FriendEntryDTO>  $ignores
     */
    public function __construct(
        public array $friends,
        public array $outgoing,
        public array $incoming,
        public array $enemies,
        public array $ignores,
    ) {}
}
