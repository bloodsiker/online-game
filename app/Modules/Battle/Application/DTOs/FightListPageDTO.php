<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\DTOs;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class FightListPageDTO
{
    /**
     * @param  LengthAwarePaginator<int, FightListItemDTO>  $fights
     */
    public function __construct(
        public string $mode,
        public LengthAwarePaginator $fights,
        public FightListFilterDTO $filter,
    ) {}
}
