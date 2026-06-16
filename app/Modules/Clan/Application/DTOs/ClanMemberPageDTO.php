<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Clan\Domain\Models\ClanRole;
use Illuminate\Support\Collection;

final readonly class ClanMemberPageDTO
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  Collection<int, ClanRole>  $allRoles
     */
    public function __construct(
        public Clan $clan,
        public ClanMember $membership,
        public Collection $rows,
        public Collection $allRoles,
        public ?ClanRole $leaderRole,
        public int $onlineCount,
        public bool $canKick,
        public bool $canInvite,
    ) {}
}
