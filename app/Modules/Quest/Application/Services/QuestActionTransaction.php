<?php

declare(strict_types=1);

namespace App\Modules\Quest\Application\Services;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestActionTransaction
{
    public function run(User $user, Quest $quest, callable $action): mixed
    {
        $clanId = $quest->isClan() ? ClanMember::where('user_id', $user->id)->value('clan_id') : null;

        return DB::transaction(function () use ($user, $quest, $clanId, $action): mixed {
            $clan = $clanId === null ? null : Clan::without('owner')->whereKey($clanId)->lockForUpdate()->firstOrFail();
            $acceptors = $clan === null ? collect() : QuestClanProgress::without(['quest', 'objectives.questObjective'])
                ->where('clan_id', $clan->id)->where('quest_id', $quest->id)->lockForUpdate()->pluck('user_id');
            $users = $acceptors->push($user->id)->filter()->unique()->sort()->mapWithKeys(
                fn ($id): array => [$id => User::without('player')->whereKey($id)->lockForUpdate()->firstOrFail()],
            );
            $lockedUser = $users->get($user->id);
            $player = Player::query()->whereKey($lockedUser->player_id)->lockForUpdate()->firstOrFail();
            $player->setRelation('user', $lockedUser);
            $lockedUser->setRelation('player', $player);
            if ($quest->isClan()) {
                $membership = ClanMember::query()->where('user_id', $lockedUser->id)->lockForUpdate()->first();
                if ($clan === null || $membership === null || (int) $membership->clan_id !== (int) $clan->id) {
                    throw ValidationException::withMessages(['quest' => 'Вы не состоите в клане.']);
                }
                $membership->setRelation('clan', $clan);
                $lockedUser->setRelation('clanMembership', $membership);
            }

            return $action($lockedUser);
        });
    }
}
