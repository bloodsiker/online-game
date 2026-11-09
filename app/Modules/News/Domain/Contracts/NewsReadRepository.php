<?php

declare(strict_types=1);

namespace App\Modules\News\Domain\Contracts;

use App\Modules\News\Infrastructure\Persistence\Models\NewsPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface NewsReadRepository
{
    /** @return Collection<int, NewsPost> */
    public function latest(int $limit): Collection;

    /** @return LengthAwarePaginator<int, NewsPost> */
    public function paginate(int $page, int $perPage): LengthAwarePaginator;
}
