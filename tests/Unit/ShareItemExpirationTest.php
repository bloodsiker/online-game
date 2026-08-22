<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ShareItemExpirationTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_calculates_ground_expiration_in_minutes(): void
    {
        Carbon::setTestNow('2026-08-17 12:00:00');

        $item = new ShareItem;
        $item->expire = 15;

        $this->assertTrue(
            Carbon::parse('2026-08-17 12:15:00')->equalTo($item->groundExpiresAt())
        );
    }

    public function test_empty_expiration_means_permanent_ground_item(): void
    {
        $item = new ShareItem;
        $item->expire = null;

        $this->assertNull($item->groundExpiresAt());
    }
}
