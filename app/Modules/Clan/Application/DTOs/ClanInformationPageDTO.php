<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;

final readonly class ClanInformationPageDTO
{
    public function __construct(
        public Clan $clan,
        public ClanMember $membership,
        public bool $canChangeNews,
    ) {}
}
