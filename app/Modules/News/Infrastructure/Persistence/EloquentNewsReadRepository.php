<?php

declare(strict_types=1);

namespace App\Modules\News\Infrastructure\Persistence;

use App\Modules\News\Domain\Contracts\NewsReadRepository;
use App\Modules\News\Infrastructure\Persistence\Models\NewsPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class EloquentNewsReadRepository implements NewsReadRepository
{
    public function latest(int $limit): Collection
    {
        return $this->query()->withCount('visibleComments')->limit($limit)->get();
    }

    public function paginate(int $page, int $perPage): LengthAwarePaginator
    {
        return $this->query()->paginate(perPage: $perPage, page: $page)->onEachSide(1);
    }

    private function query(): Builder
    {
        return NewsPost::query()->where('is_active', true)
            ->orderByDesc('created_at')->orderByDesc('id');
    }
}
