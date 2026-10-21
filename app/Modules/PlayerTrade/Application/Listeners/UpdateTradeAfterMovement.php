<?php

declare(strict_types=1);

namespace App\Modules\PlayerTrade\Application\Listeners;

use App\Modules\PlayerTrade\Application\Services\PlayerTradeService;
use App\Modules\User\Domain\Events\UserLocationChanged;

final readonly class UpdateTradeAfterMovement
{
    public function __construct(private PlayerTradeService $service) {}

    public function handle(UserLocationChanged $event): void
    {
        $this->service->participantMoved($event->userId);
    }
}
