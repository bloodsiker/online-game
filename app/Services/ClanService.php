<?php

namespace App\Services;

use App\Enums\ClanJoinRequestStatus;
use App\Enums\ClanLogAction;
use App\Enums\ClanPermission;
use App\Models\Clan\Clan;
use App\Models\Clan\ClanJoinRequest;
use App\Models\Clan\ClanLog;
use App\Models\Clan\ClanMember;
use App\Models\Clan\ClanRole;
use App\Models\User;
use App\Repositories\ClanRepository;
use Illuminate\Http\UploadedFile;

readonly class ClanService
{
    public function __construct(private ClanRepository $clanRepository) {}

    public function create(User $user, string $name, UploadedFile $icon): Clan
    {
        $iconPath = $icon->store('clan_icons', 'public');

        /** @var Clan $clan */
        $clan = $this->clanRepository->create([
            'name'     => $name,
            'owner_id' => $user->id,
            'icon'     => $iconPath,
        ]);

        $leaderRole = ClanRole::create([
            'clan_id'     => $clan->id,
            'name'        => 'Глава клана',
            'permissions' => ClanPermission::allBits(),
            'is_leader'   => true,
            'is_default'  => true,
        ]);

        foreach (['Офицер', 'Рядовой', 'Новичок'] as $roleName) {
            ClanRole::create([
                'clan_id'     => $clan->id,
                'name'        => $roleName,
                'permissions' => ClanPermission::CHAT->bit(),
                'is_leader'   => false,
                'is_default'  => true,
            ]);
        }

        $member          = new ClanMember();
        $member->clan_id = $clan->id;
        $member->user_id = $user->id;
        $member->role_id = $leaderRole->id;
        $member->save();

        $this->log($clan->id, $user->id, ClanLogAction::CLAN_CREATED, "Клан «{$name}» создан игроком {$user->name}");

        return $clan;
    }

    /**
     * @throws \RuntimeException
     */
    public function invite(User $inviter, string $nick): void
    {
        $membership = $inviter->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (!$membership->role->hasPermission(ClanPermission::INVITE)) {
            throw new \RuntimeException('У вас нет прав приглашать игроков в клан.');
        }

        $target = User::where('name', $nick)->first();

        if ($target === null) {
            throw new \RuntimeException("Игрок «{$nick}» не найден.");
        }

        if ($target->clanMembership !== null) {
            throw new \RuntimeException("Игрок «{$nick}» уже состоит в клане.");
        }

        $alreadyInvited = ClanJoinRequest::where('clan_id', $membership->clan_id)
            ->where('user_id', $target->id)
            ->exists();

        if ($alreadyInvited) {
            throw new \RuntimeException("Игрок «{$nick}» уже получил приглашение в ваш клан.");
        }

        ClanJoinRequest::create([
            'clan_id' => $membership->clan_id,
            'user_id' => $target->id,
            'status'  => ClanJoinRequestStatus::INVITE,
        ]);

        $this->log(
            $membership->clan_id,
            $inviter->id,
            ClanLogAction::INVITED,
            "Игрок {$inviter->name} пригласил {$nick} в клан"
        );
    }

    public function inviteCancel(User $inviter, ClanJoinRequest $joinRequest): void
    {
        $membership = $inviter->clanMembership;

        if ($membership === null) {
            throw new \RuntimeException('Вы не состоите в клане.');
        }

        if (!$membership->role->hasPermission(ClanPermission::INVITE)) {
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

        if (!$membership->role->hasPermission(ClanPermission::KICK)) {
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

        if (!$membership->role->hasPermission(ClanPermission::CHANGE_RANKS)) {
            throw new \RuntimeException('У вас нет прав изменять звания участников.');
        }

        $clanId = $membership->clan_id;

        $clanRoles   = ClanRole::where('clan_id', $clanId)->get()->keyBy('id');
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

        // --- Step 1: handle leadership transfer atomically before anything else ---
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

            // Demote old leader first so no two members hold the leader role simultaneously
            ClanMember::where('id', $currentLeaderMembership->id)
                ->update(['role_id' => $fallbackRole->id]);

            // Promote new leader
            ClanMember::where('clan_id', $clanId)
                ->where('user_id', $newLeaderUserId)
                ->update(['role_id' => $leaderRole->id]);

            $this->log($clanId, $user->id, ClanLogAction::PROMOTED,
                "Игрок {$user->name} передал руководство кланом игроку с ID {$newLeaderUserId}");
        }

        // --- Step 2: update remaining non-leader roles, skip leader role assignments ---
        foreach ($members as $userId => $data) {
            $roleId = (int) ($data['grade'] ?? 0);
            $userId = (int) $userId;

            if (!in_array($roleId, $clanRoleIds, true)) {
                continue;
            }

            if ($clanRoles[$roleId]->is_leader) {
                continue; // already handled above
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

        if (!$membership->role->hasPermission(ClanPermission::CHANGE_PERMS)) {
            throw new \RuntimeException('У вас нет прав управлять ролями.');
        }

        return ClanRole::create([
            'clan_id'     => $membership->clan_id,
            'name'        => mb_substr($name, 0, 16),
            'permissions' => ClanPermission::CHAT->bit(),
            'is_leader'   => false,
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

        if (!$membership->role->hasPermission(ClanPermission::CHANGE_PERMS)) {
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
                'name'        => mb_substr($data['title'] ?? $role->name, 0, 16),
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

        if (!$membership->role->hasPermission(ClanPermission::CHANGE_PERMS)) {
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

        if (!$membership->role->hasPermission(ClanPermission::CHANGE_NEWS)) {
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

        if (!$membership->role->hasPermission(ClanPermission::CHANGE_NEWS)) {
            throw new \RuntimeException('У вас нет прав изменять новости клана.');
        }

        $membership->clan->update([
            'news_1' => $news1,
            'news_2' => $news2,
            'news_3' => $news3,
        ]);

        $this->log($membership->clan_id, $user->id, ClanLogAction::CLAN_CREATED, "Игрок {$user->name} обновил новости клана");
    }

    private function log(int $clanId, ?int $userId, ClanLogAction $action, string $details): void
    {
        ClanLog::create([
            'clan_id' => $clanId,
            'user_id' => $userId,
            'action'  => $action,
            'details' => $details,
        ]);
    }
}