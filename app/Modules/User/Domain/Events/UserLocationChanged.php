<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class UserLocationChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public int $userId) {}
}
