<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

final readonly class UpgradeTransferPageDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $sourceItems
     * @param  array<int, array<string, mixed>>  $targetItems
     * @param  array<int, array<string, mixed>>  $transgressors
     */
    public function __construct(
        public Structure $blacksmith,
        public array $sourceItems,
        public array $targetItems,
        public array $transgressors,
        public string $itemTooltipScript,
    ) {}
}
