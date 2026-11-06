<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class CancelClanRequest
{
    public function __construct(
        private readonly ClanService $clanService,
        private readonly ChatService $chatService,
    ) {}

    public function execute(User $user, ClanJoinRequest $joinRequest): void
    {
        DB::transaction(function () use ($user, $joinRequest): void {
            $this->executeLocked($user, $joinRequest);
        }, attempts: 3);
    }

    private function executeLocked(User $user, ClanJoinRequest $joinRequest): void
    {
        $target = $joinRequest->user;
        $chatMessageId = $joinRequest->chat_message_id;

        $this->clanService->inviteCancel($user, $joinRequest);

        if ($target !== null) {
            $this->chatService->removeSystemMessageForUser($target, $chatMessageId);
        }
    }
}
