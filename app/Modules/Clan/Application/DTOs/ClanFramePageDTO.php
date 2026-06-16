<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\DTOs;

use App\Modules\Clan\Domain\Models\Clan;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class ClanFramePageDTO
{
    /**
     * @param  Collection<int, mixed>  $members
     */
    public function __construct(
        public Collection $members,
        public ?Clan $clan,
        public ?CarbonInterface $tenMinutesAgo,
    ) {}
}
