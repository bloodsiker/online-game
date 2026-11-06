<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptClanInvite
{
    public function __construct(
        private readonly ClanService $clanService,
        private readonly ChatService $chatService,
    ) {}

    public function execute(User $user, string $inviteUuid): Clan
    {
        return DB::transaction(function () use ($user, $inviteUuid): Clan {
            return $this->executeLocked($user, $inviteUuid);
        }, attempts: 3);
    }

    private function executeLocked(User $user, string $inviteUuid): Clan
    {
        $joinRequest = $this->clanService->acceptInvite($user, $inviteUuid);
        $clan = $joinRequest->clan;

        $this->chatService->removeSystemMessageForUser($user, $joinRequest->chat_message_id);
        $joinRequest->update(['chat_message_id' => null]);
        $this->chatService->sendClanNotice(
            (int) $clan->id,
            new LocalizedChatMessage('chat.clan_joined', ['member' => $this->memberLabel($user)]),
        );

        return $clan;
    }

    private function memberLabel(User $user): LocalizedChatMessage
    {
        return new LocalizedChatMessage('chat.party_member', [
            'name' => $user->name, 'level' => (int) ($user->player?->lvl ?? 0),
            'user_id' => $user->id, 'icon' => asset('main/images/player_info.gif'),
        ]);
    }
}
