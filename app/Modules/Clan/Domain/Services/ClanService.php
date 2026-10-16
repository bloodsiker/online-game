<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Services;

use App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Clan\Domain\Models\ClanRole;
use App\Modules\Clan\Domain\Repositories\ClanRepositoryInterface;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

readonly class ClanService
{
    public function __construct(
        private ClanRepositoryInterface $clanRepository,
        private ClanLogService $clanLogService,
    ) {}

    public function create(User $user, string $name, UploadedFile $icon): Clan
    {
        $iconPath = $icon->store('clan_icons', 'public');

        $clan = $this->clanRepository->create([
            'name' => $name,
            'owner_id' => $user->id,
            'icon' => $iconPath,
            'tax_paid_until' => now()->addMonthNoOverflow(),
        ]);

        $leaderRole = ClanRole::create([
            'clan_id' => $clan->id,
            'name' => 'Глава клана',
            'permissions' => ClanPermission::allBits(),
            'is_leader' => true,
            'is_default' => true,
        ]);

        foreach (['Офицер', 'Рядовой', 'Новичок'] as $roleName) {
            ClanRole::create([
                'clan_id' => $clan->id,
                'name' => $roleName,
                'permissions' => ClanPermission::CHAT->bit(),
                'is_leader' => false,
                'is_default' => true,
            ]);
        }

        $member = new ClanMember;
        $member->clan_id = $clan->id;
        $member->user_id = $user->id;
        $member->role_id = $leaderRole->id;
        $member->save();

        $this->log($clan->id, $user->id, ClanLogAction::CLAN_CREATED, "Клан «{$name}» создан игроком {$user->name}");

        return $clan;
    }

    public function applyToClan(User $applicant, Clan $clan): ClanJoinRequest
    {
        return DB::transaction(function () use ($applicant, $clan): ClanJoinRequest {
            User::query()->whereKey($applicant->id)->lockForUpdate()->firstOrFail();

            if (ClanMember::query()->where('user_id', $applicant->id)->exists()) {
                throw new \RuntimeException('Вы уже состоите в клане.');
            }

            $lockedClan = Clan::query()->lockForUpdate()->findOrFail($clan->id);
            if ($lockedClan->members()->count() >= 40) {
                throw new \RuntimeException('В этом клане нет свободных мест.');
            }

            $pending = ClanJoinRequest::query()
                ->where('user_id', $applicant->id)
                ->whereIn('status', [ClanJoinRequestStatus::REQUEST, ClanJoinRequestStatus::INVITE])
                ->lockForUpdate()
                ->first();

            if ($pending !== null) {
                if ((int) $pending->clan_id === (int) $lockedClan->id && $pending->status === ClanJoinRequestStatus::REQUEST) {
                    throw new \RuntimeException('Вы уже подали заявку в этот клан.');
                }

                throw new \RuntimeException('У вас уже есть активная заявка или приглашение в другой клан.');
            }

            $application = ClanJoinRequest::query()
                ->where('clan_id', $lockedClan->id)
                ->where('user_id', $applicant->id)
                ->lockForUpdate()
                ->first();

            if ($application === null) {
                $application = ClanJoinRequest::create([
                    'clan_id' => $lockedClan->id,
                    'user_id' => $applicant->id,
                    'status' => ClanJoinRequestStatus::REQUEST,
                ]);
            } else {
                $application->update([
                    'status' => ClanJoinRequestStatus::REQUEST,
                    'message' => null,
                    'uuid' => null,
                    'chat_message_id' => null,
                ]);
            }

            $this->log(
                (int) $lockedClan->id,
                $applicant->id,
                ClanLogAction::APPLICATION_SUBMITTED,
                "Игрок {$applicant->name} подал заявку на вступление в клан",
            );

            return $application->fresh();
        });
    }

    /**
     * @throws \RuntimeException
     */
    public function invite(User $inviter, string $nick): ClanJoinRequest
    {
        $membership = $inviter->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (! $membership->role->hasPermission(ClanPermission::INVITE)) {
            throw new \RuntimeException('У вас нет прав приглашать игроков в клан.');
        }

        $target = User::where('name', $nick)->first();

        if ($target === null) {
            throw new \RuntimeException("Игрок «{$nick}» не найден.");
        }

        if ($target->clanMembership !== null) {
            throw new \RuntimeException("Игрок «{$nick}» уже состоит в клане.");
        }

        // (clan_id, user_id) уникален в таблице: прошлый отклонённый/отменённый
        // запрос переиспользуется (обновляется), а не создаётся заново.
        $existing = ClanJoinRequest::where('clan_id', $membership->clan_id)
            ->where('user_id', $target->id)
            ->first();

        if ($existing !== null && $existing->status === ClanJoinRequestStatus::INVITE) {
            throw new \RuntimeException("Игрок «{$nick}» уже получил приглашение в ваш клан.");
        }

        if ($existing !== null) {
            $existing->update([
                'status' => ClanJoinRequestStatus::INVITE,
                'uuid' => (string) Str::uuid(),
                'chat_message_id' => null,
            ]);
            $joinRequest = $existing;
        } else {
            $joinRequest = ClanJoinRequest::create([
                'clan_id' => $membership->clan_id,
                'user_id' => $target->id,
                'status' => ClanJoinRequestStatus::INVITE,
                'uuid' => (string) Str::uuid(),
            ]);
        }

        $this->log(
            $membership->clan_id,
            $inviter->id,
            ClanLogAction::INVITED,
            "Игрок {$inviter->name} пригласил {$nick} в клан"
        );

        return $joinRequest;
    }

    public function inviteCancel(User $inviter, ClanJoinRequest $joinRequest): void
    {
        $membership = $inviter->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (! $membership->role->hasPermission(ClanPermission::INVITE)) {
            throw new \RuntimeException('У вас нет прав приглашать игроков в клан.');
        }

        $joinRequest->delete();

        $this->log(
            $membership->clan_id,
            $inviter->id,
            ClanLogAction::INVITED_CANCEL,
            "Игрок {$inviter->name} отменил приглашение на вступление {$joinRequest->user->name} в клан"
        );
    }

    public function acceptApplication(User $actor, ClanJoinRequest $application): ClanJoinRequest
    {
        $membership = $actor->clanMembership;
        if ($membership === null || (int) $membership->clan_id !== (int) $application->clan_id) {
            throw new \RuntimeException('Вы не можете обработать эту заявку.');
        }
        if (! $membership->role->hasPermission(ClanPermission::INVITE)) {
            throw new \RuntimeException('У вас нет прав принимать игроков в клан.');
        }

        $applicationId = (int) $application->id;

        return DB::transaction(function () use ($actor, $applicationId): ClanJoinRequest {
            $lockedApplication = ClanJoinRequest::query()->lockForUpdate()->findOrFail($applicationId);
            if ($lockedApplication->status !== ClanJoinRequestStatus::REQUEST) {
                throw new \RuntimeException('Заявка уже обработана.');
            }

            $clan = Clan::query()->lockForUpdate()->findOrFail($lockedApplication->clan_id);
            $target = User::query()->whereKey($lockedApplication->user_id)->lockForUpdate()->firstOrFail();
            if (ClanMember::query()->where('user_id', $target->id)->exists()) {
                throw new \RuntimeException('Игрок уже состоит в клане.');
            }
            if ($clan->members()->count() >= 40) {
                throw new \RuntimeException('В клане нет свободных мест.');
            }

            $entryRole = ClanRole::query()
                ->where('clan_id', $clan->id)
                ->where('is_leader', false)
                ->where('is_default', true)
                ->orderBy('id')
                ->first();
            if ($entryRole === null) {
                throw new \RuntimeException('В клане не настроено звание для новых участников.');
            }

            ClanMember::create([
                'clan_id' => $clan->id,
                'user_id' => $target->id,
                'role_id' => $entryRole->id,
            ]);
            $lockedApplication->update(['status' => ClanJoinRequestStatus::ACCEPTED]);

            $this->log(
                (int) $clan->id,
                $actor->id,
                ClanLogAction::APPLICATION_ACCEPTED,
                "Заявка игрока {$target->name} принята",
            );

            return $lockedApplication->fresh(['clan', 'user']);
        });
    }

    public function declineApplication(User $actor, ClanJoinRequest $application): void
    {
        $membership = $actor->clanMembership;
        if ($membership === null || (int) $membership->clan_id !== (int) $application->clan_id) {
            throw new \RuntimeException('Вы не можете обработать эту заявку.');
        }
        if (! $membership->role->hasPermission(ClanPermission::INVITE)) {
            throw new \RuntimeException('У вас нет прав отклонять заявки в клан.');
        }

        DB::transaction(function () use ($actor, $application): void {
            $lockedApplication = ClanJoinRequest::query()->lockForUpdate()->findOrFail($application->id);
            if ($lockedApplication->status !== ClanJoinRequestStatus::REQUEST) {
                throw new \RuntimeException('Заявка уже обработана.');
            }

            $lockedApplication->update(['status' => ClanJoinRequestStatus::DECLINED]);
            $this->log(
                (int) $lockedApplication->clan_id,
                $actor->id,
                ClanLogAction::APPLICATION_DECLINED,
                "Заявка игрока {$lockedApplication->user->name} отклонена",
            );
        });
    }

    public function withdrawApplication(User $applicant, ClanJoinRequest $application): void
    {
        if ((int) $application->user_id !== (int) $applicant->id || $application->status !== ClanJoinRequestStatus::REQUEST) {
            throw new \RuntimeException('Вы не можете отменить эту заявку.');
        }

        DB::transaction(function () use ($applicant, $application): void {
            $lockedApplication = ClanJoinRequest::query()->lockForUpdate()->findOrFail($application->id);
            if ((int) $lockedApplication->user_id !== (int) $applicant->id || $lockedApplication->status !== ClanJoinRequestStatus::REQUEST) {
                throw new \RuntimeException('Эта заявка уже обработана.');
            }

            $lockedApplication->update(['status' => ClanJoinRequestStatus::CANCELLED]);
            $this->log(
                (int) $lockedApplication->clan_id,
                $applicant->id,
                ClanLogAction::APPLICATION_WITHDRAWN,
                "Игрок {$applicant->name} отозвал заявку на вступление",
            );
        });
    }

    /**
     * Принятие приглашения самим приглашённым игроком: создаёт ClanMember
     * с ролью по умолчанию для нового участника.
     *
     * @throws \RuntimeException
     */
    public function acceptInvite(User $user, string $inviteUuid): ClanJoinRequest
    {
        $joinRequest = ClanJoinRequest::where('uuid', $inviteUuid)
            ->where('status', ClanJoinRequestStatus::INVITE)
            ->first();

        if ($joinRequest === null || (int) $joinRequest->user_id !== (int) $user->id) {
            throw new \RuntimeException('Приглашение не найдено или уже обработано.');
        }

        if ($user->clanMembership !== null) {
            $joinRequest->update(['status' => ClanJoinRequestStatus::CANCELLED]);
            throw new \RuntimeException('Вы уже состоите в клане.');
        }

        $clan = $joinRequest->clan;
        // Та же роль, в которую откатывается глава при передаче руководства —
        // единственный однозначный "начальный" ранг среди дефолтных ролей клана.
        $entryRole = ClanRole::where('clan_id', $clan->id)
            ->where('is_leader', false)
            ->where('is_default', true)
            ->orderBy('id')
            ->first();

        if ($entryRole === null) {
            throw new \RuntimeException('В клане не настроена роль для новых участников.');
        }

        DB::transaction(function () use ($joinRequest, $user, $clan, $entryRole): void {
            $member = new ClanMember;
            $member->clan_id = $clan->id;
            $member->user_id = $user->id;
            $member->role_id = $entryRole->id;
            $member->save();

            $joinRequest->update(['status' => ClanJoinRequestStatus::ACCEPTED]);
        });

        $this->log(
            $clan->id,
            $user->id,
            ClanLogAction::INVITE_ACCEPTED,
            "Игрок {$user->name} принял приглашение и вступил в клан"
        );

        return $joinRequest->fresh(['clan', 'user']);
    }

    /**
     * @throws \RuntimeException
     */
    public function declineInvite(User $user, string $inviteUuid): ClanJoinRequest
    {
        $joinRequest = ClanJoinRequest::where('uuid', $inviteUuid)
            ->where('status', ClanJoinRequestStatus::INVITE)
            ->first();

        if ($joinRequest === null || (int) $joinRequest->user_id !== (int) $user->id) {
            throw new \RuntimeException('Приглашение не найдено или уже обработано.');
        }

        $joinRequest->update(['status' => ClanJoinRequestStatus::DECLINED]);

        $this->log(
            $joinRequest->clan_id,
            $user->id,
            ClanLogAction::INVITE_DECLINED,
            "Игрок {$user->name} отклонил приглашение в клан"
        );

        return $joinRequest;
    }

    /**
     * @throws \RuntimeException
     */
    public function leaveClan(User $user): void
    {
        $membership = $user->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if ($membership->role->is_leader) {
            throw new \RuntimeException('Глава клана не может покинуть клан. Сначала передайте руководство другому игроку.');
        }

        $clanId = $membership->clan_id;

        $membership->delete();

        $this->log($clanId, $user->id, ClanLogAction::LEFT, "Игрок {$user->name} покинул клан");
    }

    /**
     * @throws \RuntimeException
     */
    public function kickMember(User $kicker, User $target): void
    {
        $membership = $kicker->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (! $membership->role->hasPermission(ClanPermission::KICK)) {
            throw new \RuntimeException('У вас нет прав исключать игроков из клана.');
        }

        if ($kicker->id === $target->id) {
            throw new \RuntimeException('Вы не можете исключить себя из клана.');
        }

        $targetMembership = ClanMember::where('clan_id', $membership->clan_id)
            ->where('user_id', $target->id)
            ->with('role')
            ->first();

        if ($targetMembership === null) {
            throw new \RuntimeException('Игрок не состоит в вашем клане.');
        }

        if ($targetMembership->role->is_leader) {
            throw new \RuntimeException('Нельзя исключить главу клана.');
        }

        $targetMembership->delete();

        $this->log(
            $membership->clan_id,
            $kicker->id,
            ClanLogAction::KICKED,
            "Игрок {$kicker->name} исключил {$target->name} из клана"
        );
    }

    /**
     * @throws \RuntimeException
     */
    public function saveMemberRoles(User $user, array $members): void
    {
        $membership = $user->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (! $membership->role->hasPermission(ClanPermission::CHANGE_RANKS)) {
            throw new \RuntimeException('У вас нет прав изменять звания участников.');
        }

        $clanId = $membership->clan_id;

        $clanRoles = ClanRole::where('clan_id', $clanId)->get()->keyBy('id');
        $clanRoleIds = $clanRoles->keys()->all();

        $leaderRole = $clanRoles->first(fn ($r) => $r->is_leader);

        $leaderAssignCount = collect($members)
            ->filter(fn ($data) => (int) ($data['grade'] ?? 0) === (int) $leaderRole?->id)
            ->count();

        if ($leaderAssignCount > 1) {
            throw new \RuntimeException('Нельзя сохранить изменения, в клане не может быть несколько глав клана!');
        }

        $currentLeaderMembership = ClanMember::where('clan_id', $clanId)
            ->where('role_id', $leaderRole?->id)
            ->first();

        $newLeaderUserId = null;
        if ($leaderRole) {
            foreach ($members as $userId => $data) {
                if ((int) ($data['grade'] ?? 0) === $leaderRole->id) {
                    $newLeaderUserId = (int) $userId;
                    break;
                }
            }
        }

        if ($newLeaderUserId !== null
            && $newLeaderUserId !== $currentLeaderMembership?->user_id
            && $currentLeaderMembership?->user_id === $user->id
        ) {
            $fallbackRole = ClanRole::where('clan_id', $clanId)
                ->where('is_leader', false)
                ->where('is_default', true)
                ->orderBy('id')
                ->first();

            ClanMember::where('id', $currentLeaderMembership->id)
                ->update(['role_id' => $fallbackRole->id]);

            ClanMember::where('clan_id', $clanId)
                ->where('user_id', $newLeaderUserId)
                ->update(['role_id' => $leaderRole->id]);

            $this->log($clanId, $user->id, ClanLogAction::PROMOTED,
                "Игрок {$user->name} передал руководство кланом игроку с ID {$newLeaderUserId}");
        }

        foreach ($members as $userId => $data) {
            $roleId = (int) ($data['grade'] ?? 0);
            $userId = (int) $userId;

            if (! in_array($roleId, $clanRoleIds, true)) {
                continue;
            }

            if ($clanRoles[$roleId]->is_leader) {
                continue;
            }

            ClanMember::where('clan_id', $clanId)
                ->where('user_id', $userId)
                ->whereHas('role', fn ($q) => $q->where('is_leader', false))
                ->update(['role_id' => $roleId]);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function addRole(User $user, string $name): ClanRole
    {
        $membership = $user->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (! $membership->role->hasPermission(ClanPermission::CHANGE_PERMS)) {
            throw new \RuntimeException('У вас нет прав управлять ролями.');
        }

        return ClanRole::create([
            'clan_id' => $membership->clan_id,
            'name' => mb_substr($name, 0, 16),
            'permissions' => ClanPermission::CHAT->bit(),
            'is_leader' => false,
        ]);
    }

    /**
     * @throws \RuntimeException
     */
    public function saveRoles(User $user, array $grades): void
    {
        $membership = $user->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (! $membership->role->hasPermission(ClanPermission::CHANGE_PERMS)) {
            throw new \RuntimeException('У вас нет прав изменять полномочия.');
        }

        $clanId = $membership->clan_id;

        foreach ($grades as $roleId => $data) {
            /** @var ClanRole|null $role */
            $role = ClanRole::where('id', $roleId)->where('clan_id', $clanId)->first();

            if ($role === null || $role->is_leader) {
                continue;
            }

            $permissions = 0;

            foreach (($data['flags'] ?? []) as $slug => $checked) {
                $permission = ClanPermission::tryFrom($slug);

                if ($permission !== null && $checked) {
                    $permissions |= $permission->bit();
                }
            }

            $role->update([
                'name' => mb_substr($data['title'] ?? $role->name, 0, 16),
                'permissions' => $permissions,
            ]);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function deleteRole(User $user, ClanRole $role): void
    {
        $membership = $user->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if ($role->clan_id !== $membership->clan_id) {
            throw new \RuntimeException('Роль не принадлежит вашему клану.');
        }

        if ($role->is_default) {
            throw new \RuntimeException('Нельзя удалить стандартную роль клана.');
        }

        if (! $membership->role->hasPermission(ClanPermission::CHANGE_PERMS)) {
            throw new \RuntimeException('У вас нет прав управлять ролями.');
        }

        $membersWithRole = ClanMember::where('clan_id', $membership->clan_id)
            ->where('role_id', $role->id)
            ->count();

        if ($membersWithRole > 0) {
            throw new \RuntimeException("Нельзя удалить звание: у {$membersWithRole} игр. установлено это звание. Сначала измените им звание.");
        }

        $role->delete();
    }

    /**
     * @throws \RuntimeException
     */
    public function saveDescription(User $user, string $description): void
    {
        $membership = $user->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (! $membership->role->hasPermission(ClanPermission::CHANGE_NEWS)) {
            throw new \RuntimeException('У вас нет прав изменять описание клана.');
        }

        $membership->clan->update(['description' => $description]);

        $this->log($membership->clan_id, $user->id, ClanLogAction::CLAN_CREATED, "Игрок {$user->name} обновил описание клана");
    }

    public function saveNews(User $user, string $news1, string $news2, string $news3): void
    {
        $membership = $user->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (! $membership->role->hasPermission(ClanPermission::CHANGE_NEWS)) {
            throw new \RuntimeException('У вас нет прав изменять новости клана.');
        }

        $membership->clan->update([
            'news_1' => $news1,
            'news_2' => $news2,
            'news_3' => $news3,
        ]);

        $this->log($membership->clan_id, $user->id, ClanLogAction::CLAN_CREATED, "Игрок {$user->name} обновил новости клана");
    }

    public function addBonusPoints(ClanMember $member, int $amount): void
    {
        $member->increment('points', $amount);
        $member->refresh();

        $this->log(
            $member->clan_id,
            $member->user_id,
            ClanLogAction::BONUS_POINTS_EARNED,
            "Заработано бонусных очков: +{$amount} (итого: {$member->points})"
        );
    }

    private function log(int $clanId, ?int $userId, ClanLogAction $action, string $details): void
    {
        $this->clanLogService->write($clanId, $userId, $action, $details);
    }
}
