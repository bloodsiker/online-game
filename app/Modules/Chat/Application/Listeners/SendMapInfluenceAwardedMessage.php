<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Influence\Domain\Events\MapInfluenceAwarded;

final readonly class SendMapInfluenceAwardedMessage
{
    public function __construct(private ChatService $chatService) {}

    public function handle(MapInfluenceAwarded $event): void
    {
        $this->chatService->sendQuestToUser(
            $event->user,
            sprintf(
                'Ваше влияние на карте <b>«%s»</b> увеличено на <b>+%d</b>.',
                e($event->mapName),
                $event->amount,
            ),
        );
    }
}
