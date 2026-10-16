<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Dungeon\Domain\Events\ClanBossRunStarted;

final class SendClanBossRunStartedMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(ClanBossRunStarted $event): void
    {
        $this->chatService->sendClanNotice(
            $event->clanId,
            new LocalizedChatMessage('chat.clan_boss_run_started', [
                'dungeon' => ['translations' => $event->dungeonNameTranslations],
                'boss' => ['translations' => $event->bossNameTranslations],
            ]),
        );
    }
}
