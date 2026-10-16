<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Interface\Domain\Events\PlayerCameOnline;

final class SendClanMemberOnlineMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(PlayerCameOnline $event): void
    {
        $this->chatService->sendClanNotice(
            $event->clanId,
            new LocalizedChatMessage('chat.clan_member_online', ['user_id' => $event->userId]),
        );
    }
}
