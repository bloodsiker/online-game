<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Repositories;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ChatMessageRepositoryInterface
{
    public function create(array $data): ChatMessage;

    public function getForChannel(
        User $user,
        ChatChannel $channel,
        ?int $afterId,
        int $limit,
    ): Collection;

    public function filterValidIds(User $user, ChatChannel $channel, array $ids): array;

    public function getIgnoredUserIds(User $user): array;
}