<?php

declare(strict_types=1);

namespace App\Modules\Influence\Domain\Events;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MapInfluenceAwarded implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public readonly string $mapName,
        public readonly int $amount,
        public readonly int $total,
    ) {}
}
