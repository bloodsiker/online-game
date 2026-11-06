<?php

declare(strict_types=1);

namespace App\Modules\News\Application\UseCases;

use App\Modules\News\Infrastructure\Persistence\Models\LaunchSubscriber;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetLaunchSubscriptions
{
    /** @return LengthAwarePaginator<int, LaunchSubscriber> */
    public function execute(string $search = '', string $status = 'all', string $locale = 'all'): LengthAwarePaginator
    {
        return LaunchSubscriber::query()
            ->when($search !== '', fn ($query) => $query->where('email', 'like', '%'.mb_strtolower(trim($search)).'%'))
            ->when($status === 'pending', fn ($query) => $query->whereNull('notified_at'))
            ->when($status === 'sent', fn ($query) => $query->whereNotNull('notified_at'))
            ->when($locale !== 'all', fn ($query) => $query->where('locale', $locale))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();
    }
}
