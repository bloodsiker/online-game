<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Location;

use App\Modules\Location\Domain\Events\GatheringMapUpdated;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use PHPUnit\Framework\TestCase;

final class GatheringMapUpdatedTest extends TestCase
{
    public function test_broadcast_is_queued_after_commit_instead_of_blocking_the_request(): void
    {
        $event = new GatheringMapUpdated(7, 10, 'attempt-started');

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $event);
        $this->assertNotInstanceOf(ShouldBroadcastNow::class, $event);
    }
}
