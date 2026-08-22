<?php

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\DTOs\FightDTO;
use App\Modules\Battle\Application\Services\Combat\Boss\BossMechanicsService;
use App\Modules\Battle\Application\Services\Combat\Boss\BossPhaseService;
use App\Modules\Battle\Application\Services\Combat\Boss\BossShieldService;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRoundHit;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\Auth;

readonly class FightOrchestrator
{
    public function __construct(
        private BattleRepository $battleRepository,
        private AttackService $attackService,
        private PlayerDeathService $playerDeathService,
        private MonsterAttackService $monsterAttackService,
        private BattleFinishService $finishService,
        private BossMechanicsService $bossMechanicsService,
        private BossPhaseService $bossPhaseService,
        private BossShieldService $shieldService,
        private BattleEffectService $effectService,
    ) {}

    public function attack(int $id, int $monsterId, int $action): FightDTO
    {
        return \DB::transaction(function () use ($id, $monsterId, $action) {
            $user = Auth::user();
            $player = Player::query()
                ->whereKey($user->player->id)
                ->lockForUpdate()
                ->firstOrFail();
            $battleLocation = $user->currentLocation;
            $fightDTO = new FightDTO;

            $battle = $this->battleRepository->getOneById($id);
            $battle->increment('rounds');

            $attackedMonster = BattleDetail::with(['locationMonster.monster'])
                ->where(['location_monster_id' => $monsterId])
                ->lockForUpdate()
                ->first();

            $attackedPlayer = BattleDetail::with('user')
                ->where([
                    'battle_id' => $battle->id,
                    'user_id' => $user->id,
                ])
                ->lockForUpdate()
                ->first();

            $battleRound = $this->createRound(
                $battle,
                $user->id,
                $attackedMonster->location_monster_id,
            );

            if ($attackedMonster->status->isDeath()) {
                return $fightDTO->setBattle($battle)
                    ->setBattleRound($battleRound)
                    ->setAttackedMonster($attackedMonster)
                    ->setPlayer($player)
                    ->setAttackedMonster(null);
            }

            $locationMonster = $attackedMonster->locationMonster;
            $monster = $locationMonster->monster;
            $isBoss = $monster->isBoss();

            // player log: effects + player attack + boss mechanics + kill events
            $playerLog = new AttackResultDTO;
            // monster log: only the monster's counterattack
            $monsterLog = new AttackResultDTO;

            if ($isBoss) {
                $this->shieldService->updateShieldDuration($battle, $playerLog);
            }

            $playerIsStunned = $this->effectService->processPlayerEffects($player, $battle, $playerLog);
            $monsterIsStunned = $this->effectService->processMonsterEffects($locationMonster, $battle, $playerLog);

            if (! $playerIsStunned) {
                $xpMultiplier = (float) ($user->currentLocation->dungeon?->xp_multiplier ?? 1.0);
                $attackLog = $this->attackService->execute($player, $locationMonster, $action, $battle, $xpMultiplier);
                $playerLog->merge($attackLog);
            }

            $monsterHpAfterPlayerAttack = $locationMonster->hp_now;

            if ($isBoss && $locationMonster->hp_now > 0) {
                $this->bossPhaseService->checkAndTriggerPhase($locationMonster, $battle, $playerLog);
                $this->bossMechanicsService->processMechanics($locationMonster, $battle, $player, $playerLog);
            }

            if ($locationMonster->hp_now > 0 && ! $monsterIsStunned) {
                if ($isBoss) {
                    $this->monsterAttackService->executeBossAttack($player, $locationMonster, $battle, $monsterLog);
                } else {
                    $this->monsterAttackService->execute($player, $locationMonster, $monsterLog);
                }
            }

            if ($locationMonster->hp_now <= 0) {
                $this->attackService->handleMonsterDeath($player, $locationMonster, $attackedMonster, $playerLog);

                if ($isBoss) {
                    $this->bossMechanicsService->handleBossDeath($locationMonster, $battle, $player, $playerLog);
                }
            }

            $player->save();
            $playerHpAfterRound = $player->hp_now;

            $fullLog = (new AttackResultDTO)->merge($playerLog)->merge($monsterLog);

            if ($player->hp_now <= 0) {
                $fightDTO = $this->playerDeathService->handle(
                    $player,
                    $battle,
                    $battleRound,
                    $attackedPlayer,
                    $attackedMonster,
                    $fullLog,
                );

                $this->saveHits($battleRound, $user->id, $attackedMonster->location_monster_id, $playerHpAfterRound, $monsterHpAfterPlayerAttack, $playerLog, $monsterLog);
                $this->finishService->checkAndFinish($battle, $battleLocation, $user);

                return $fightDTO;
            }

            $user->save();
            $locationMonster->save();

            $this->attackService->checkLevelUp($player, $playerLog);

            $finishDTO = $this->finishService->checkAndFinish($battle, $battleLocation, $user);

            $fullLog = (new AttackResultDTO)->merge($playerLog)->merge($monsterLog);
            $battleRound->action = $fullLog->getLog();
            $battleRound->save();

            $this->saveHits($battleRound, $user->id, $attackedMonster->location_monster_id, $playerHpAfterRound, $monsterHpAfterPlayerAttack, $playerLog, $monsterLog);

            if ($isBoss) {
                $this->saveBossMetadata($battle, $battleRound, $locationMonster);
            }

            return $fightDTO
                ->setBattle($finishDTO->battle ?? $battle)
                ->setBattleRound($battleRound)
                ->setAttackedMonster($attackedMonster)
                // fresh() returns the updated scalar state without reloading every
                // relation that was used for the stat calculation in this round.
                ->setPlayer($player->fresh())
                ->setSideLog($fullLog->getSideLog());
        });
    }

    private function createRound(Battle $battle, int $userId, int $monsterId): BattleRound
    {
        $battleRound = new BattleRound;
        $battleRound->battle_id = $battle->id;
        $battleRound->round_number = $battle->rounds;
        $battleRound->user_id = $userId;
        $battleRound->location_monster_id = $monsterId;

        return $battleRound;
    }

    private function saveHits(
        BattleRound $round,
        int $userId,
        int $monsterId,
        int $playerHpAfter,
        int $monsterHpAfter,
        AttackResultDTO $playerLog,
        AttackResultDTO $monsterLog,
    ): void {
        if ($playerLog->getLog() !== '') {
            BattleRoundHit::create([
                'battle_round_id' => $round->id,
                'participant_type' => 'user',
                'participant_id' => $userId,
                'hp_after' => $playerHpAfter,
                'action' => $playerLog->getLog(),
            ]);
        }

        if ($monsterLog->getLog() !== '') {
            BattleRoundHit::create([
                'battle_round_id' => $round->id,
                'participant_type' => 'monster',
                'participant_id' => $monsterId,
                'hp_after' => $monsterHpAfter,
                'action' => $monsterLog->getLog(),
            ]);
        }
    }

    private function saveBossMetadata(Battle $battle, BattleRound $round, $locationMonster): void
    {
        $metadata = $battle->boss_metadata ?? [];

        $metadata['current_phase'] = $locationMonster->current_phase ?? 1;
        $metadata['mechanics_triggered'] = array_merge(
            $metadata['mechanics_triggered'] ?? [],
            $round->triggered_mechanics ?? []
        );

        $battle->boss_metadata = $metadata;
        $battle->save();
    }
}
