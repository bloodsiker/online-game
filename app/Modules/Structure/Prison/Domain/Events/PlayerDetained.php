<?php

declare(strict_types=1);

namespace App\Modules\Structure\Prison\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;

/** Игрок помещён в Темницу; $durationMinutes = null — бессрочно. */
final readonly class PlayerDetained
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public ?int $durationMinutes,
        public ?string $reason,
    ) {}
}
