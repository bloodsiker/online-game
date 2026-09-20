<?php

declare(strict_types=1);

namespace App\Modules\Scheduler\Domain\DTOs;

use App\Modules\Scheduler\Domain\Enums\ScheduledTaskFrequency;

final readonly class ScheduledTaskDefinition
{
    /**
     * @param  list<ScheduledTaskFrequency>  $allowedFrequencies
     */
    public function __construct(
        public string $key,
        public string $name,
        public string $description,
        public ScheduledTaskFrequency $defaultFrequency,
        public array $allowedFrequencies,
        public bool $settingsMutable = true,
        public int $lockSeconds = 300,
        public int $successHistoryIntervalSeconds = 300,
    ) {}

    public function allows(ScheduledTaskFrequency $frequency): bool
    {
        return in_array($frequency, $this->allowedFrequencies, true);
    }
}
