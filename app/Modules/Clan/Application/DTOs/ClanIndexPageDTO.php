<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use Illuminate\Support\Collection;

final readonly class ClanIndexPageDTO
{
    /**
     * @param  Collection<int, mixed>  $activeQuests
     */
    public function __construct(
        public bool $inClan,
        public Collection $activeQuests,
        public bool $isLeader,
    ) {}
}
