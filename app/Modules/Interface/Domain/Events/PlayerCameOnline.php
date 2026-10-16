<?php

declare(strict_types=1);

namespace App\Modules\Interface\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class PlayerCameOnline
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public int $clanId,
    ) {}
}
