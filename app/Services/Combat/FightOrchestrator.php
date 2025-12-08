<?php

namespace App\Services\Combat;

use App\DTO\FightDTO;
use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Battle\BattleRound;
use App\Repositories\BattleRepository;
use Illuminate\Support\Facades\Auth;

readonly class FightOrchestrator
{
    public function __construct(
        private BattleRepository $battleRepository,
        private AttackService $attackService,
        private PlayerDeathService $playerDeathService,
        private MonsterAttackService $monsterAttackService,
        private BattleFinishService $finishService,
    ) {}

    public function attack(int $id, int $monsterId, int $action): FightDTO
    {
        $user = Auth::user();
        $player = $user->player;
        $fightDTO = new FightDTO();

        $battle = $this->battleRepository->getOneById($id);

        $attackedMonster = BattleDetail::with('locationMonster')->where(['location_monster_id' => $monsterId])->first();
        $attackedPlayer = BattleDetail::with('user')->where(['user_id' => $user->id])->first();

        $battleRound = $this->createRound($battle, $user->id, $attackedMonster->location_monster_id);

        if ($attackedMonster->status !== 1) {
            return $fightDTO->setBattle($battle)
                ->setBattleRound($battleRound)
                ->setAttackedMonster($attackedMonster)
                ->setPlayer($player)
                ->setAttackedMonster(null);
        }

        $locationMonster = $attackedMonster->locationMonster;

        $roundLog = $this->attackService->execute($player, $locationMonster, $action);

        if ($locationMonster->hp_now > 0) {
            $this->monsterAttackService->execute($player, $locationMonster, $roundLog);
        }

        $player->save();

        if ($player->hp_now <= 0) {
            return $this->playerDeathService->handle($player, $battle, $battleRound, $attackedPlayer, $attackedMonster, $roundLog);
        }

        if ($locationMonster->hp_now <= 0) {
            $this->attackService->handleMonsterDeath($player, $locationMonster, $attackedMonster, $roundLog);
        }

        $user->save();
        $locationMonster->save();

        $this->attackService->checkLevelUp($player, $roundLog);

        $finishDTO = $this->finishService->checkAndFinish($battle, $user->currentLocation);

        $battleRound->action = $roundLog->getLog();
        $battleRound->save();

        return $fightDTO
            ->setBattle($finishDTO->battle ?? $battle)
            ->setBattleRound($battleRound)
            ->setAttackedMonster($attackedMonster)
            ->setPlayer($player);
    }

    private function createRound(Battle $battle, int $userId, int $monsterId): BattleRound
    {
        $battleRound = new BattleRound();
        $battleRound->battle_id = $battle->id;
        $battleRound->round_number = $battle->rounds;
        $battleRound->user_id = $userId;
        $battleRound->location_monster_id = $monsterId;

        return $battleRound;
    }
}
