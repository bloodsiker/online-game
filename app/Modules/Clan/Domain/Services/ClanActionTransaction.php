<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Services;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Clan\Domain\Models\ClanRole;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClanActionTransaction
{
    public function clanId(User $actor): int
    {
        $id = ClanMember::where('user_id', $actor->id)->value('clan_id');
        if ($id === null) {
            throw new RuntimeException('Вы не состоите в клане.');
        }

        return (int) $id;
    }

    /** Clan → sorted users → memberships → roles; cached caller relations are never trusted. */
    public function run(int $clanId, Collection $userIds, callable $action, bool $allMembers = false): mixed
    {
        return DB::transaction(function () use ($clanId, $userIds, $action, $allMembers): mixed {
            $clan = Clan::without('owner')->whereKey($clanId)->lockForUpdate()->firstOrFail();
            $ids = $allMembers
                ? $userIds->merge(ClanMember::where('clan_id', $clanId)->pluck('user_id'))
                : $userIds;
            $users = $ids->filter()->unique()->sort()->mapWithKeys(
                fn ($id): array => [$id => User::without('player')->whereKey($id)->lockForUpdate()->firstOrFail()],
            );
            $members = ClanMember::whereIn('user_id', $users->keys())->orderBy('user_id')->lockForUpdate()->get()->keyBy('user_id');
            $roles = ClanRole::where('clan_id', $clanId)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($users as $user) {
                $member = $members->get($user->id);
                if ($member !== null && (int) $member->clan_id === $clanId) {
                    $member->setRelation('clan', $clan);
                    $member->setRelation('role', $roles->get($member->role_id));
                }
                $user->setRelation('clanMembership', $member);
            }

            return $action($clan, $users);
        }, attempts: 3);
    }
}
