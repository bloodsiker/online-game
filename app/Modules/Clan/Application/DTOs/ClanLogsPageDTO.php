<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\ClanMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ClanLogsPageDTO
{
    /**
     * @param  array<int, mixed>  $actions
     */
    public function __construct(
        public LengthAwarePaginator $logs,
        public array $actions,
        public ClanMember $membership,
        public ?string $filterAction,
        public ?string $filterUser,
    ) {}
}
