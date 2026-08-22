<?php

declare(strict_types=1);

namespace App\Modules\Quest\Domain\Events;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class QuestItemDropped implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $recipient,
        public int $shareItemId,
    ) {}
}
