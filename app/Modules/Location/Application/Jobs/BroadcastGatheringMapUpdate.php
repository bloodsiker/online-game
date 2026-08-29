<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\Jobs;

use App\Modules\Location\Domain\Events\GatheringMapUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class BroadcastGatheringMapUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $mapId,
        public readonly ?int $nodeId,
        public readonly string $reason,
    ) {}

    public function handle(): void
    {
        GatheringMapUpdated::dispatch($this->mapId, $this->nodeId, $this->reason);
    }
}
