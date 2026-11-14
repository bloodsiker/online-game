<?php

declare(strict_types=1);

namespace App\Modules\Gift\Application\DTOs;

use App\Modules\Gift\Infrastructure\Persistence\Models\PlayerGift;
use Illuminate\Support\Collection;

final readonly class PlayerGiftsBlockDTO
{
    /** @param Collection<int, PlayerGift> $gifts */
    public function __construct(
        public Collection $gifts,
        public int $total,
        public string $allUrl,
    ) {}
}
