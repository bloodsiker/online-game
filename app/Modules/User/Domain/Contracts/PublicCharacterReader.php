<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Contracts;

interface PublicCharacterReader
{
    /** @return array{id: int, name: string, level: int|null}|null */
    public function find(int $userId): ?array;
}
