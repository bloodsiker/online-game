<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

/** Результат пополнения колчана: сообщение и свежий тултип колчана для фрейма экипировки. */
final readonly class FillQuiverResultDTO
{
    /** @param array<string, mixed>|null $quiverTooltip */
    public function __construct(
        public bool $ok,
        public string $message,
        public ?int $quiverItemId = null,
        public ?array $quiverTooltip = null,
    ) {}
}
