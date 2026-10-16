<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\Services;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Clan\Application\Notifications\ClanApplicationReceivedNotification;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

final readonly class ClanApplicationService
{
    public function __construct(
        private ClanService $clanService,
        private ClanSkillService $clanSkillService,
        private ChatService $chatService,
    ) {}

    public function apply(User $applicant, Clan $clan): ClanJoinRequest
    {
        $application = $this->clanService->applyToClan($applicant, $clan);
        $applicant->loadMissing('player');

        $this->chatService->sendClanNotice(
            (int) $clan->id,
            new LocalizedChatMessage('chat.clan_application_received', [
                'member' => $this->memberLabel($applicant),
            ]),
        );

        $leader = $clan->members()
            ->whereHas('role', fn ($query) => $query->where('is_leader', true))
            ->with('user')
            ->first()?->user ?? $clan->owner;

        if ($leader?->email) {
            try {
                Notification::route('mail', $leader->email)->notify(
                    new ClanApplicationReceivedNotification(
                        clanName: $clan->name,
                        applicantName: $applicant->name,
                        applicantLevel: (int) ($applicant->player?->lvl ?? 0),
                    ),
                );
            } catch (Throwable $exception) {
                Log::warning('Failed to email clan leader about a join application.', [
                    'clan_id' => $clan->id,
                    'applicant_id' => $applicant->id,
                    'leader_id' => $leader->id,
                    'exception' => $exception,
                ]);
            }
        }

        return $application;
    }

    public function accept(User $actor, ClanJoinRequest $application): void
    {
        $application = $this->clanService->acceptApplication($actor, $application);
        $member = $application->user;
        $clan = $application->clan;

        if ($member->player !== null) {
            $this->clanSkillService->applyAllSkillsToPlayer($member->player, $clan);
        }

        $this->chatService->sendClanNotice(
            (int) $clan->id,
            new LocalizedChatMessage('chat.clan_joined', ['member' => $this->memberLabel($member)]),
        );
    }

    public function decline(User $actor, ClanJoinRequest $application): void
    {
        $this->clanService->declineApplication($actor, $application);
    }

    public function withdraw(User $applicant, ClanJoinRequest $application): void
    {
        $this->clanService->withdrawApplication($applicant, $application);
    }

    private function memberLabel(User $user): LocalizedChatMessage
    {
        $user->loadMissing('player');

        return new LocalizedChatMessage('chat.party_member', [
            'name' => $user->name,
            'level' => (int) ($user->player?->lvl ?? 0),
            'user_id' => $user->id,
            'icon' => asset('main/images/player_info.gif'),
        ]);
    }
}
