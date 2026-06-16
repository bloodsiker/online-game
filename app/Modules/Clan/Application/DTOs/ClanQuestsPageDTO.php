<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use Illuminate\Support\Collection;

final readonly class ClanQuestsPageDTO
{
    /**
     * @param  Collection<int, mixed>  $activeQuests
     * @param  Collection<int, mixed>  $availableQuests
     * @param  Collection<int, mixed>  $history
     */
    public function __construct(
        public Clan $clan,
        public ClanMember $membership,
        public bool $isLeader,
        public Collection $activeQuests,
        public Collection $availableQuests,
        public Collection $history,
    ) {}
}
