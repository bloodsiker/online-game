<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use Illuminate\Support\Collection;

final readonly class ClanSkillsPageDTO
{
    /**
     * @param  Collection<int, mixed>  $definitions
     * @param  Collection<int, mixed>  $learnedMap
     * @param  Collection<int, int>  $backpackShareItemCounts
     */
    public function __construct(
        public Clan $clan,
        public ClanMember $membership,
        public Collection $definitions,
        public Collection $learnedMap,
        public bool $canLearn,
        public Collection $backpackShareItemCounts,
        public mixed $player,
        public mixed $playerDecorator,
    ) {}
}
