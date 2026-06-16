<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class ClanContextDTO
{
    public function __construct(
        public User $user,
        public ?ClanMember $membership,
        public ?Clan $clan,
    ) {}
}
