<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Domain\Contracts;

interface BackpackPreviewRenderer
{
    /** @param list<array<string, mixed>> $items */
    public function render(array $items): string;
}
