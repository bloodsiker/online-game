<?php

declare(strict_types=1);

namespace App\Modules\Interface\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OnlinePlayersReader
{
    public function count(): int;

    public function page(int $page, int $perPage): LengthAwarePaginator;
}
