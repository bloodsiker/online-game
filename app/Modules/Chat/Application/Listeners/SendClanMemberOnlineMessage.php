<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Interface\Domain\Events\PlayerCameOnline;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class SendClanMemberOnlineMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(PlayerCameOnline $event): void
    {
        $user = User::query()->with('player')->find($event->userId);

        if ($user === null) {
            return;
        }

        $this->chatService->sendClanNotice(
            $event->clanId,
            new LocalizedChatMessage('chat.clan_member_online', [
                'member' => new LocalizedChatMessage('chat.party_member', [
                    'name' => $user->name,
                    'level' => (int) ($user->player?->lvl ?? 0),
                    'user_id' => $user->id,
                    'icon' => asset('main/images/player_info.gif'),
                ]),
            ]),
        );
    }
}
