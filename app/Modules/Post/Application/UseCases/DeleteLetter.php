<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\UseCases;

use App\Modules\User\Infrastructure\Persistence\Models\User;

class DeleteLetter
{
    public function __construct(private readonly BulkLetters $bulkLetters) {}

    /** Sender and recipient delete only their own copy; a deleted attachment cannot be claimed. */
    public function execute(User $user, int $letterId): bool
    {
        return $this->bulkLetters->execute($user, [$letterId], 'delete') > 0;
    }
}
