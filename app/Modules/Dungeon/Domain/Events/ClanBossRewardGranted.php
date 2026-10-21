<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class ClanBossRewardGranted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public int $receiptId) {}
}
