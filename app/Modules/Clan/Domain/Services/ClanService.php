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
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

readonly class ClanService
{
    public function __construct(
        private ClanRepositoryInterface $clanRepository,
        private ClanLogService $clanLogService,
        private ClanActionTransaction $actions,
        private ClanSkillService $skills,
    ) {}

    public function create(User $user, string $name, UploadedFile $icon): Clan
    {
        $iconPath = $icon->store('clan_icons', 'public');

        try {
            return DB::transaction(function () use ($user, $name, $iconPath): Clan {
                $user = User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
                if (ClanMember::where('user_id', $user->id)->exists()) {
                    throw new \RuntimeException('Вы уже состоите в клане.');
                }
                if ((int) $user->location_id !== 29) {
                    throw new \RuntimeException(__('clan.directory.location_required'));
                }
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
            }, attempts: 3);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($iconPath);
            throw $exception;
        }
    }

    public function applyToClan(User $applicant, Clan $clan): ClanJoinRequest
    {
        return $this->actions->run(clanId: (int) $clan->id, userIds: collect([$applicant->id]), action: fn (Clan $lockedClan, Collection $users) => $this->applyToClanLocked($users->get($applicant->id), $lockedClan));
    }

    private function applyToClanLocked(User $applicant, Clan $clan): ClanJoinRequest
    {
        return DB::transaction(function () use ($applicant, $clan): ClanJoinRequest {
            User::query()->whereKey($applicant->id)->lockForUpdate()->firstOrFail();

            if ($applicant->clanMembership !== null) {
                throw new \RuntimeException('Вы уже состоите в клане.');
            }

            $lockedClan = Clan::query()->lockForUpdate()->findOrFail($clan->id);
            if ($lockedClan->members()->lockForUpdate()->get()->count() >= 40) {
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
        $target = User::without('player')->where('name', $nick)->first();
        if ($target === null) {
            throw new \RuntimeException("Игрок «{$nick}» не найден.");
        }

        return $this->actions->run(clanId: $this->actions->clanId($inviter), userIds: collect([$inviter->id, $target->id]), action: function (Clan $clan, Collection $users) use ($inviter, $target): ClanJoinRequest {
            $actor = $users->get($inviter->id);
            $this->requireMembership($actor, (int) $clan->id);

            return $this->inviteLocked($actor, $users->get($target->id));
        });
    }

    private function inviteLocked(User $inviter, User $target): ClanJoinRequest
    {
        $membership = $inviter->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (! $membership->role->hasPermission(ClanPermission::INVITE)) {
            throw new \RuntimeException('У вас нет прав приглашать игроков в клан.');
        }

        $nick = $target->name;
        if ($target->clanMembership !== null) {
            throw new \RuntimeException("Игрок «{$nick}» уже состоит в клане.");
        }

        if ($membership->clan->members()->lockForUpdate()->get()->count() >= 40) {
            throw new \RuntimeException('В клане нет свободных мест.');
        }
        if (ClanJoinRequest::where('user_id', $target->id)
            ->whereIn('status', [ClanJoinRequestStatus::REQUEST, ClanJoinRequestStatus::INVITE])
            ->lockForUpdate()->exists()) {
            throw new \RuntimeException(__('clan.action.pending'));
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
        $this->actions->run(clanId: (int) $joinRequest->clan_id, userIds: collect([$inviter->id, $joinRequest->user_id]), action: function (Clan $clan, Collection $users) use ($inviter, $joinRequest): void {
            $actor = $users->get($inviter->id);
            $this->requireMembership($actor, (int) $clan->id);
            $request = $this->lockedRequest($joinRequest);
            if ($request->status !== ClanJoinRequestStatus::INVITE) {
                throw new \RuntimeException('Приглашение уже обработано.');
            }
            $this->inviteCancelLocked($actor, $request);
        });
    }

    private function inviteCancelLocked(User $inviter, ClanJoinRequest $joinRequest): void
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
        return $this->actions->run(clanId: (int) $application->clan_id, userIds: collect([$actor->id, $application->user_id]), action: function (Clan $clan, Collection $users) use ($actor, $application): ClanJoinRequest {
            $actor = $users->get($actor->id);
            $this->requireMembership($actor, (int) $clan->id);
            $request = $this->acceptApplicationLocked($actor, $this->lockedRequest($application));
            $this->applySkills($users->get($request->user_id), $clan);

            return $request;
        });
    }

    private function acceptApplicationLocked(User $actor, ClanJoinRequest $application): ClanJoinRequest
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
            $targetMembership = ClanMember::query()->where('user_id', $target->id)->lockForUpdate()->first();
            if ($targetMembership !== null) {
                throw new \RuntimeException((int) $targetMembership->clan_id !== (int) $clan->id
                    ? __('clan.application.already_in_another_clan')
                    : __('Игрок уже состоит в клане.'));
            }
            if ($clan->members()->lockForUpdate()->get()->count() >= 40) {
                throw new \RuntimeException('В клане нет свободных мест.');
            }

            $entryRole = ClanRole::query()
                ->where('clan_id', $clan->id)
                ->where('is_leader', false)
                ->where('is_default', true)
                ->orderBy('id')
                ->lockForUpdate()->first();
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
        $this->actions->run(clanId: (int) $application->clan_id, userIds: collect([$actor->id, $application->user_id]), action: function (Clan $clan, Collection $users) use ($actor, $application): void {
            $actor = $users->get($actor->id);
            $this->requireMembership($actor, (int) $clan->id);
            $this->declineApplicationLocked($actor, $this->lockedRequest($application));
        });
    }

    private function declineApplicationLocked(User $actor, ClanJoinRequest $application): void
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
        $this->actions->run(clanId: (int) $application->clan_id, userIds: collect([$applicant->id, $application->user_id]), action: fn (Clan $clan, Collection $users) => $this->withdrawApplicationLocked($users->get($applicant->id), $this->lockedRequest($application)));
    }

    private function withdrawApplicationLocked(User $applicant, ClanJoinRequest $application): void
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
        $request = $this->findInvite($user, $inviteUuid);

        return $this->actions->run(clanId: (int) $request->clan_id, userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($user, $inviteUuid): ClanJoinRequest {
            $user = $users->get($user->id);
            $request = $this->acceptInviteLocked($user, $inviteUuid);
            $this->applySkills($user, $clan);

            return $request;
        });
    }

    private function acceptInviteLocked(User $user, string $inviteUuid): ClanJoinRequest
    {
        $joinRequest = ClanJoinRequest::where('uuid', $inviteUuid)
            ->where('status', ClanJoinRequestStatus::INVITE)
            ->lockForUpdate()->first();

        if ($joinRequest === null || (int) $joinRequest->user_id !== (int) $user->id) {
            throw new \RuntimeException('Приглашение не найдено или уже обработано.');
        }

        if ($user->clanMembership !== null) {
            $joinRequest->update(['status' => ClanJoinRequestStatus::CANCELLED]);
            throw new \RuntimeException('Вы уже состоите в клане.');
        }

        $clan = $joinRequest->clan;
        if ($clan->members()->lockForUpdate()->get()->count() >= 40) {
            throw new \RuntimeException('В клане нет свободных мест.');
        }
        // Та же роль, в которую откатывается глава при передаче руководства —
        // единственный однозначный "начальный" ранг среди дефолтных ролей клана.
        $entryRole = ClanRole::where('clan_id', $clan->id)
            ->where('is_leader', false)
            ->where('is_default', true)
            ->orderBy('id')
            ->lockForUpdate()->first();

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
        $request = $this->findInvite($user, $inviteUuid);

        return $this->actions->run(clanId: (int) $request->clan_id, userIds: collect([$user->id]), action: fn (Clan $clan, Collection $users) => $this->declineInviteLocked($users->get($user->id), $inviteUuid));
    }

    private function declineInviteLocked(User $user, string $inviteUuid): ClanJoinRequest
    {
        $joinRequest = ClanJoinRequest::where('uuid', $inviteUuid)
            ->where('status', ClanJoinRequestStatus::INVITE)
            ->lockForUpdate()->first();

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
        $this->actions->run(clanId: $this->actions->clanId($user), userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($user): void {
            $actor = $users->get($user->id);
            $this->requireMembership($actor, (int) $clan->id);
            $this->leaveClanLocked($actor);
            $this->removeSkills($actor, $clan);
        });
    }

    private function leaveClanLocked(User $user): void
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
        $this->actions->run(clanId: $this->actions->clanId($kicker), userIds: collect([$kicker->id, $target->id]), action: function (Clan $clan, Collection $users) use ($kicker, $target): void {
            $kicker = $users->get($kicker->id);
            $target = $users->get($target->id);
            $this->requireMembership($kicker, (int) $clan->id);
            $this->kickMemberLocked($kicker, $target);
            $this->removeSkills($target, $clan);
        });
    }

    private function kickMemberLocked(User $kicker, User $target): void
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
        $this->actions->run(clanId: $this->actions->clanId($user), userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($user, $members): void {
            $actor = $users->get($user->id);
            $this->requireMembership($actor, (int) $clan->id);
            $this->saveMemberRolesLocked($actor, $members);
        }, allMembers: true);
    }

    private function saveMemberRolesLocked(User $user, array $members): void
    {
        $membership = $user->clanMembership;
        if (! $membership->role->hasPermission(ClanPermission::CHANGE_RANKS)) {
            throw new \RuntimeException('У вас нет прав изменять звания участников.');
        }
        $clan = $membership->clan;
        $roles = ClanRole::where('clan_id', $clan->id)->get()->keyBy('id');
        $roster = ClanMember::where('clan_id', $clan->id)->get()->keyBy('user_id');
        $leaderRole = $roles->first(fn ($role) => $role->is_leader);
        $currentLeader = $leaderRole === null ? null : $roster->firstWhere('role_id', $leaderRole->id);
        if ($currentLeader === null) {
            throw new \RuntimeException(__('clan.action.missing_leader'));
        }
        $requested = collect($members)->map(fn ($data): int => (int) ($data['grade'] ?? 0));
        foreach ($requested as $userId => $roleId) {
            if (! $roster->has((int) $userId) || ! $roles->has($roleId)) {
                throw new \RuntimeException(__('clan.action.stale_roster'));
            }
        }
        $leaders = $requested->filter(fn ($roleId): bool => $roleId === (int) $leaderRole->id)->keys();
        if ($leaders->count() > 1) {
            throw new \RuntimeException(__('clan.action.multiple_leaders'));
        }
        $newLeaderId = (int) ($leaders->first() ?? $currentLeader->user_id);
        if ($newLeaderId !== (int) $currentLeader->user_id) {
            if ((int) $currentLeader->user_id !== (int) $user->id) {
                throw new \RuntimeException(__('clan.action.leader_only'));
            }
            $fallback = $roles->first(fn ($role) => ! $role->is_leader && $role->is_default);
            if ($fallback === null) {
                throw new \RuntimeException(__('clan.action.missing_fallback'));
            }
            $currentLeader->update(['role_id' => $fallback->id]);
            $roster->get($newLeaderId)->update(['role_id' => $leaderRole->id]);
            $clan->update(['owner_id' => $newLeaderId]);
            $this->log((int) $clan->id, $user->id, ClanLogAction::PROMOTED,
                "Игрок {$user->name} передал руководство кланом игроку с ID {$newLeaderId}");
        } elseif ($requested->has($currentLeader->user_id) && $requested->get($currentLeader->user_id) !== (int) $leaderRole->id) {
            throw new \RuntimeException(__('clan.action.select_leader'));
        }
        foreach ($requested as $userId => $roleId) {
            if ((int) $userId !== $newLeaderId && $roleId !== (int) $leaderRole->id) {
                $roster->get((int) $userId)->update(['role_id' => $roleId]);
            }
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function addRole(User $user, string $name): ClanRole
    {
        return $this->actions->run(clanId: $this->actions->clanId($user), userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($user, $name): ClanRole {
            $actor = $users->get($user->id);
            $this->requireMembership($actor, (int) $clan->id);

            return $this->addRoleLocked($actor, $name);
        }, allMembers: false);
    }

    private function addRoleLocked(User $user, string $name): ClanRole
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
        $this->actions->run(clanId: $this->actions->clanId($user), userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($user, $grades): void {
            $actor = $users->get($user->id);
            $this->requireMembership($actor, (int) $clan->id);
            $this->saveRolesLocked($actor, $grades);
        }, allMembers: true);
    }

    private function saveRolesLocked(User $user, array $grades): void
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
        $this->actions->run(clanId: $this->actions->clanId($user), userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($user, $role): void {
            $actor = $users->get($user->id);
            $this->requireMembership($actor, (int) $clan->id);
            $this->deleteRoleLocked($actor, ClanRole::whereKey($role->id)->where('clan_id', $clan->id)->lockForUpdate()->firstOrFail());
        }, allMembers: false);
    }

    private function deleteRoleLocked(User $user, ClanRole $role): void
    {
        $membership = $user->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if ($role->clan_id !== $membership->clan_id) {
            throw new \RuntimeException('Роль не принадлежит вашему клану.');
        }

        if ($role->is_default || $role->is_leader) {
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
        $this->actions->run(clanId: $this->actions->clanId($user), userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($user, $description): void {
            $actor = $users->get($user->id);
            $this->requireMembership($actor, (int) $clan->id);
            $this->saveDescriptionLocked($actor, $description);
        }, allMembers: false);
    }

    private function saveDescriptionLocked(User $user, string $description): void
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
        $this->actions->run(clanId: $this->actions->clanId($user), userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($user, $news1, $news2, $news3): void {
            $actor = $users->get($user->id);
            $this->requireMembership($actor, (int) $clan->id);
            $this->saveNewsLocked($actor, $news1, $news2, $news3);
        }, allMembers: false);
    }

    private function saveNewsLocked(User $user, string $news1, string $news2, string $news3): void
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

    private function requireMembership(User $user, int $clanId): void
    {
        if ($user->clanMembership === null || (int) $user->clanMembership->clan_id !== $clanId || $user->clanMembership->role === null) {
            throw new \RuntimeException(__('clan.action.membership_changed'));
        }
    }

    private function lockedRequest(ClanJoinRequest $observed): ClanJoinRequest
    {
        $request = ClanJoinRequest::whereKey($observed->id)->where('clan_id', $observed->clan_id)
            ->where('user_id', $observed->user_id)->lockForUpdate()->first();
        if ($request === null || $request->uuid !== $observed->uuid) {
            throw new \RuntimeException(__('clan.action.request_changed'));
        }

        return $request;
    }

    private function findInvite(User $user, string $uuid): ClanJoinRequest
    {
        $request = ClanJoinRequest::where('uuid', $uuid)->where('user_id', $user->id)->first();
        if ($request === null) {
            throw new \RuntimeException('Приглашение не найдено или уже обработано.');
        }

        return $request;
    }

    private function applySkills(User $user, Clan $clan): void
    {
        if ($clan->hasPaidTax() && $user->player_id !== null) {
            $player = Player::whereKey($user->player_id)->lockForUpdate()->first();
            if ($player !== null) {
                $this->skills->applyAllSkillsToPlayer($player, $clan);
            }
        }
    }

    private function removeSkills(User $user, Clan $clan): void
    {
        if ($user->player_id !== null) {
            $player = Player::whereKey($user->player_id)->lockForUpdate()->first();
            if ($player !== null) {
                $this->skills->removeAllSkillsFromPlayer($player, $clan);
            }
        }
    }

    private function log(int $clanId, ?int $userId, ClanLogAction $action, string $details): void
    {
        $this->clanLogService->write($clanId, $userId, $action, $details);
    }
}
