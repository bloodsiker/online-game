<?php

declare(strict_types=1);

namespace App\Modules\Gift\Infrastructure\Persistence;

use App\Modules\Gift\Domain\Contracts\PlayerGiftRepository;
use App\Modules\Gift\Infrastructure\Persistence\Models\PlayerGift;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentPlayerGiftRepository implements PlayerGiftRepository
{
    public function latestFor(int $recipientId, int $limit): Collection
    {
        return $this->query($recipientId)->limit($limit)->get();
    }

    public function countFor(int $recipientId): int
    {
        return PlayerGift::query()->where('recipient_id', $recipientId)->count();
    }

    public function paginateFor(int $recipientId, int $perPage): LengthAwarePaginator
    {
        return $this->query($recipientId)->paginate($perPage);
    }

    public function create(int $recipientId, ?int $senderId, int $shareItemId, ?string $message, bool $isAnonymous): PlayerGift
    {
        return PlayerGift::query()->create([
            'recipient_id' => $recipientId,
            'sender_id' => $senderId,
            'share_item_id' => $shareItemId,
            'message' => $message,
            'is_anonymous' => $isAnonymous,
        ]);
    }

    /** @return Builder<PlayerGift> */
    private function query(int $recipientId): Builder
    {
        return PlayerGift::query()->where('recipient_id', $recipientId)
            ->with(['shareItem', 'sender' => fn ($query) => $query->without('player')->select(['id', 'name'])])
            ->orderByDesc('id');
    }
}
