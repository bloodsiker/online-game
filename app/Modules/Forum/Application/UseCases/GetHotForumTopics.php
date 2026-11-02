<?php

declare(strict_types=1);

namespace App\Modules\Forum\Application\UseCases;

use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopic;
use Illuminate\Database\Eloquent\Collection;

final class GetHotForumTopics
{
    /** @return Collection<int, ForumTopic> */
    public function execute(): Collection
    {
        return ForumTopic::query()
            ->whereHas('section', fn ($query) => $query->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('parent_id')
                    ->orWhereHas('parent', fn ($parent) => $parent->where('is_active', true))))
            ->orderByDesc('last_post_at')->orderByDesc('id')
            ->limit(5)->get();
    }
}
