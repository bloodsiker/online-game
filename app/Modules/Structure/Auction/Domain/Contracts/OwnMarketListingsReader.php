<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OwnMarketListingsReader
{
    public function auction(int $userId, int $page, int $perPage): LengthAwarePaginator;

    public function exchange(int $userId, int $page, int $perPage): LengthAwarePaginator;
}
