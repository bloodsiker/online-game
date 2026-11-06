<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\UseCases;

use App\Modules\Battle\Application\DTOs\FightDTO;
use App\Modules\Battle\Application\Services\Battle\BattleActionLocks;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class RunAway
{
    public function __construct(private readonly BattleActionLocks $locks, private readonly RandomizerInterface $random) {}

    /** Null is a successful escape; a failed attempt returns its committed round. */
    public function execute(User $user, int $battleId): ?FightDTO
    {
        $ids = $this->locks->userIds($battleId, (int) $user->id);

        return DB::transaction(function () use ($user, $battleId, $ids): ?FightDTO {
            $user = $this->locks->users($ids)->get($user->id);
            $player = Player::whereKey($user->player_id)->lockForUpdate()->firstOrFail();
            $battle = Battle::whereKey($battleId)->lockForUpdate()->firstOrFail();
            $participant = BattleDetail::where('battle_id', $battleId)->where('user_id', $user->id)->lockForUpdate()->first();
            abort_if(! $battle->status->isActive() || $participant === null || ! $participant->status->isLife()
                || (int) $user->location_id !== (int) $battle->location_id || $player->hp_now <= 0, 409, __('Вы уже покинули этот бой.'));
            abort_if(BattleDetail::where('battle_id', $battleId)->whereHas('locationMonster', fn ($query) => $query->whereNotNull('clan_boss_stage_state_id'))->exists(),
                409, __('Из боя с клановым боссом можно выйти безопасно через кнопку «Выйти из боя».'));
            $success = $this->random->between(0, 100) > 50;
            abort_if($success && $user->prev_location_id === null, 409, __('Некуда отступать.'));
            $battle->increment('rounds');
            $monster = $success ? null : BattleDetail::with('locationMonster.monster')->where('battle_id', $battleId)
                ->whereNotNull('location_monster_id')->where('status', BattleDetailStatus::LIFE)->orderBy('id')->first();
            $round = BattleRound::create([
                'battle_id' => $battleId, 'round_number' => $battle->rounds, 'user_id' => $user->id,
                'location_monster_id' => $monster?->location_monster_id,
                'action' => $success ? '<p>Вы бежали на восток...</p>' : '<p>Вы попытались бежать... не получилось.</p>',
            ]);
            if (! $success) {
                return (new FightDTO)->setBattle($battle)->setBattleRound($round)->setPlayer($player)->setAttackedMonster($monster);
            }
            $participant->status = BattleDetailStatus::DEATH;
            $participant->save();
            if (! BattleDetail::where('battle_id', $battleId)->whereNotNull('user_id')->where('status', BattleDetailStatus::LIFE)->exists()) {
                $battle->update(['status' => BattleStatus::FINISH]);
            }
            [$user->location_id, $user->prev_location_id] = [$user->prev_location_id, $user->location_id];
            $user->save();

            return null;
        });
    }
}
