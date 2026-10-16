<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ClanDirectoryPageDTO
{
    /**
     * @param  LengthAwarePaginator<int, mixed>  $clans
     * @param  Collection<int, mixed>  $applications  Keyed by clan ID.
     */
    public function __construct(
        public LengthAwarePaginator $clans,
        public Collection $applications,
    ) {}
}
