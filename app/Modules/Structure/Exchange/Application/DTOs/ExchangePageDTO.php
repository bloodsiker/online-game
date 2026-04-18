<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\DTOs;

final readonly class ExchangePageDTO
{
    /**
     * @param  list<ExchangeViewItemDTO>  $items
     */
    public function __construct(
        public int $exchangeId,
        public int $money,
        public int $diamonds,
        public array $items,
    ) {}
}
