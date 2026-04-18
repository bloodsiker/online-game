<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\DTOs;

final readonly class OnMapPageDTO
{
    public function __construct(
        public string $view,
    ) {}
}
