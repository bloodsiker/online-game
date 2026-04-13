<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Models\User;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetMessages
{
    public function __construct(
        private readonly ChatMessageRepositoryInterface $repository,
    ) {}

    /**
     * If $afterId is null, returns last $limit messages in chronological order.
     * If $afterId is set, returns messages newer than that ID.
     */
    public function execute(User $user, ChatChannel $channel, ?int $afterId = null, int $limit = 60): Collection
    {
        return $this->repository->getForChannel($user, $channel, $afterId, $limit);
    }

    public function filterValidIds(User $user, ChatChannel $channel, array $ids): array
    {
        return $this->repository->filterValidIds($user, $channel, $ids);
    }
}