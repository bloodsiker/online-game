<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Repositories;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\User\Infrastructure\Persistence\Models\User;

interface ChatMessageRepositoryInterface
{
    public function create(array $data): ChatMessage;

    /** @return ChatMessage[] */
    public function getForChannel(
        User $user,
        ChatChannel $channel,
        ?int $afterId,
        int $limit,
    ): array;

    public function filterValidIds(User $user, ChatChannel $channel, array $ids): array;

    public function getIgnoredUserIds(User $user): array;
}
