<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Monster;

use App\Modules\Monster\Domain\Events\BossMoved;
use Illuminate\Broadcasting\Channel;
use PHPUnit\Framework\TestCase;

class BossMovedEventTest extends TestCase
{
    public function test_event_goes_only_to_the_channel_of_its_location(): void
    {
        $event = new BossMoved(991, BossMoved::ARRIVED, ['ru' => 'Чрево Кургана'], 'north');

        $this->assertInstanceOf(Channel::class, $event->broadcastOn());
        $this->assertSame('location.991', $event->broadcastOn()->name);
        $this->assertSame('boss.moved', $event->broadcastAs());
        $this->assertSame(
            ['type' => 'arrived', 'names' => ['ru' => 'Чрево Кургана'], 'direction' => 'north'],
            $event->broadcastWith(),
        );
    }
}
