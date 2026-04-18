<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\DTOs;

use Illuminate\Support\Collection;

final readonly class ReputationListPageDTO
{
    public function __construct(
        public Collection $playerReputations,
        public string $group,
    ) {}
}
