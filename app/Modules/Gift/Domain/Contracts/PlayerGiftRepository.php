<?php

declare(strict_types=1);

namespace App\Modules\Gift\Domain\Contracts;

use App\Modules\Gift\Infrastructure\Persistence\Models\PlayerGift;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PlayerGiftRepository
{
    /** @return Collection<int, PlayerGift> последние подарки игрока, новые первыми */
    public function latestFor(int $recipientId, int $limit): Collection;

    public function countFor(int $recipientId): int;

    /** @return LengthAwarePaginator<PlayerGift> */
    public function paginateFor(int $recipientId, int $perPage): LengthAwarePaginator;

    public function create(int $recipientId, ?int $senderId, int $shareItemId, ?string $message, bool $isAnonymous): PlayerGift;
}
