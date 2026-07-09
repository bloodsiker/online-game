<?php

declare(strict_types=1);

namespace App\Modules\Event\Application\DTOs;

final readonly class ActivityCardDTO
{
    public function __construct(
        public string $title,
        public string $icon,
        public int $iconCount,
        public int $progressCurrent,
        public int $progressTotal,
        public string $descriptionHtml,
        public ?string $bonusHtml = null,
    ) {
    }

    public function isRewardReady(): bool
    {
        return $this->progressTotal > 0 && $this->progressCurrent >= $this->progressTotal;
    }
}