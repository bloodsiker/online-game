<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class WorldEventStarted
{
    use Dispatchable;

    public function __construct(
        public readonly int $eventId,
        public readonly int $runId,
        public readonly string $title,
        public readonly string $mapName,
        public readonly ?string $mapSlug,
    ) {}
}
