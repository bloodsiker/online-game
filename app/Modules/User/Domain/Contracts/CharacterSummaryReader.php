<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Contracts;

interface CharacterSummaryReader
{
    /** @return array{nickname: string, level: int, money: int, diamonds: string}|null */
    public function read(int $userId): ?array;
}
