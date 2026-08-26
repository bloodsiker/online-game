<?php

namespace App\Modules\Player\Application\Listeners;

use App\Modules\Player\Domain\Events\PlayerDied;
use App\Modules\Player\Domain\Services\ExperienceService;

readonly class RemoveExpOnDeathListener
{
    public function __construct(
        private ExperienceService $experienceService,
    ) {}

    public function handle(PlayerDied $event): void
    {
        $this->experienceService->lostExpAfterDeath($event->player);
    }
}
