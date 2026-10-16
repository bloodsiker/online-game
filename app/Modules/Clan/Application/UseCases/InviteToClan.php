<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class InviteToClan
{
    public function __construct(
        private readonly ClanService $clanService,
        private readonly ChatService $chatService,
    ) {}

    public function execute(User $user, ?string $nickname): void
    {
        if ($nickname === null || $nickname === '') {
            throw new RuntimeException('Введите ник игрока.');
        }

        $joinRequest = $this->clanService->invite($user, $nickname);
        $joinRequest->loadMissing('user', 'clan');
        $clan = $joinRequest->clan;

        $clanLink = new LocalizedChatMessage('chat.clan_link', [
            'url' => route('clan.public', ['clan' => $clan->id]),
            'icon' => $clan->icon
                ? new LocalizedChatMessage('chat.clan_icon', [
                    'url' => Storage::disk('public')->url($clan->icon),
                ])
                : '',
            'name' => $clan->name,
        ]);

        $message = $this->chatService->sendClanInviteToUser(
            $joinRequest->user,
            new LocalizedChatMessage('chat.clan_invite', [
                'member' => $this->memberLabel($user),
                'clan' => $clanLink,
                'accept_url' => route('clan.invite.accept', ['inviteUuid' => $joinRequest->uuid]),
                'decline_url' => route('clan.invite.decline', ['inviteUuid' => $joinRequest->uuid]),
            ]),
        );
        $joinRequest->update(['chat_message_id' => $message->id]);
    }

    private function memberLabel(User $user): LocalizedChatMessage
    {
        return new LocalizedChatMessage('chat.party_member', [
            'name' => $user->name, 'level' => (int) ($user->player?->lvl ?? 0),
            'user_id' => $user->id, 'icon' => asset('main/images/player_info.gif'),
        ]);
    }
}
