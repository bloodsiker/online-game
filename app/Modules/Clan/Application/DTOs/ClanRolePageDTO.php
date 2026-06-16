<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use Illuminate\Support\Collection;

final readonly class ClanRolePageDTO
{
    /**
     * @param  Collection<int, mixed>  $roles
     * @param  array<int, mixed>  $permissions
     */
    public function __construct(
        public Clan $clan,
        public ClanMember $membership,
        public Collection $roles,
        public array $permissions,
        public bool $canChangePerms,
    ) {}
}
