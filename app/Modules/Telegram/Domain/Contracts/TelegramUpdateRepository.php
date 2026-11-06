<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Domain\Contracts;

use Closure;

interface TelegramUpdateRepository
{
    /** @param Closure(): array<string, mixed> $reply
     * @return array<string, mixed>
     */
    public function once(int $updateId, Closure $reply): array;

    public function prune(): int;
}
