<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Player\Domain\Events\PlayerInjured;

final readonly class SendPlayerInjuryMessage
{
    public function __construct(private ChatService $chatService) {}

    public function handle(PlayerInjured $event): void
    {
        $injury = $event->injury;
        $durationMinutes = max(
            1,
            (int) ceil($injury->applied_at->diffInSeconds($injury->expires_at) / 60),
        );

        $this->chatService->sendSystemToUser(
            $event->player->user,
            sprintf(
                '<b style="color:#ba0000">Получена травма: %s на %d мин.</b>',
                e($injury->displayName()),
                $durationMinutes,
            ),
        );
    }
}
