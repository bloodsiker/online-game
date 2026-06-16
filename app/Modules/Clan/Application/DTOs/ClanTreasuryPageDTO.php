<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ClanTreasuryPageDTO
{
    public function __construct(
        public Structure $clanWarehouse,
        public Clan $clan,
        public ClanMember $membership,
        public bool $canWithdraw,
        public LengthAwarePaginator $logs,
    ) {}
}
