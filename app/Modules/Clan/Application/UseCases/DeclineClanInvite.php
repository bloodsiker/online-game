<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class DeclineClanInvite
{
    public function __construct(
        private readonly ClanService $clanService,
        private readonly ChatService $chatService,
    ) {}

    public function execute(User $user, string $inviteUuid): void
    {
        $joinRequest = $this->clanService->declineInvite($user, $inviteUuid);

        $this->chatService->removeSystemMessageForUser($user, $joinRequest->chat_message_id);
        $joinRequest->update(['chat_message_id' => null]);
        $this->chatService->sendClanNotice(
            (int) $joinRequest->clan_id,
            new LocalizedChatMessage('chat.clan_declined', ['member' => $this->memberLabel($user)]),
        );
    }

    private function memberLabel(User $user): LocalizedChatMessage
    {
        return new LocalizedChatMessage('chat.party_member', [
            'name' => $user->name, 'level' => (int) ($user->player?->lvl ?? 0),
            'user_id' => $user->id, 'icon' => asset('main/images/player_info.gif'),
        ]);
    }
}
