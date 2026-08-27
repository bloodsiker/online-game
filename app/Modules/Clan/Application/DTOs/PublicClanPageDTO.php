<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanRole;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final readonly class PublicClanPageDTO
{
    /**
     * @param Collection<int, array{
     *     user: User,
     *     role: ClanRole|null,
     *     is_online: bool
     * }> $members
     */
    public function __construct(
        public Clan $clan,
        public string $mode,
        public int $membersCount,
        public int $levelRank,
        public int $experienceRank,
        public Collection $members,
        public LengthAwarePaginator $logs,
    ) {}
}
