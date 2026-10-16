<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final class ClanBossRunExpired implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /** @param array<string, string> $dungeonNameTranslations */
    public function __construct(
        public readonly int $clanId,
        public readonly array $dungeonNameTranslations,
    ) {}
}
