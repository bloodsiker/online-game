<?php

namespace App\Listeners\Player\Death;

use App\Events\PlayerDied;
use App\Services\ExperienceService;

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
