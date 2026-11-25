<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Player\Domain\Events\PlayerChangeStat;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Tests\TestCase;

class PlayerChangeStatEventTest extends TestCase
{
    public function test_event_restores_resources_by_default_for_level_up(): void
    {
        $this->assertTrue((new PlayerChangeStat(new Player))->restoreResources);
        $this->assertFalse((new PlayerChangeStat(new Player, restoreResources: false))->restoreResources);
    }
}
