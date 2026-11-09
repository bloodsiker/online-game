<?php

declare(strict_types=1);

namespace App\Modules\News\Application\UseCases;

use App\Modules\News\Domain\Contracts\NewsReadRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetNewsFeed
{
    public function __construct(private NewsReadRepository $news) {}

    public function execute(bool $archive, int $page = 1): Collection|LengthAwarePaginator
    {
        return $archive ? $this->news->paginate(page: $page, perPage: 10) : $this->news->latest(limit: 5);
    }
}
