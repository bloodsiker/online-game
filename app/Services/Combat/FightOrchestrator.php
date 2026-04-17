<?php

namespace App\Services\Combat;

use App\DTO\AttackResultDTO;
use App\DTO\FightDTO;
use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Battle\BattleRound;
use App\Repositories\BattleRepository;
use App\Services\Combat\Boss\BossMechanicsService;
use App\Services\Combat\Boss\BossPhaseService;
use App\Services\Combat\Boss\BossShieldService;
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
            $player = $user->player;
            $fightDTO = new FightDTO;

            $battle = $this->battleRepository->getOneById($id);
            $battle->increment('rounds');

            $attackedMonster = BattleDetail::with(['locationMonster.monster'])
                ->where(['location_monster_id' => $monsterId])
                ->lockForUpdate() // Блокуємо для конкурентних запитів
                ->first();

            $attackedPlayer = BattleDetail::with('user')
                ->where(['user_id' => $user->id])
                ->first();

            $battleRound = $this->createRound($battle, $user->id, $attackedMonster->location_monster_id);

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

            $effectLog = new AttackResultDTO;

            if ($isBoss) {
                $this->shieldService->updateShieldDuration($battle, $effectLog);
            }

            // Process active effects on player (DoT tick, stun check)
            $playerIsStunned = $this->effectService->processPlayerEffects($player, $battle, $effectLog);

            // Process active effects on monster (DoT tick, stun check)
            $monsterIsStunned = $this->effectService->processMonsterEffects($locationMonster, $battle, $effectLog);

            $xpMultiplier = (float) ($user->currentLocation->dungeon?->xp_multiplier ?? 1.0);

            if (! $playerIsStunned) {
                $roundLog = $this->attackService->execute($player, $locationMonster, $action, $battle, $xpMultiplier);
                $effectLog->merge($roundLog);
            }

            $roundLog = $effectLog;

            // BOSS: Перевірка фаз ПЕРЕД атакою боса
            if ($isBoss && $locationMonster->hp_now > 0) {
                $this->bossPhaseService->checkAndTriggerPhase(
                    $locationMonster,
                    $battle,
                    $roundLog
                );
            }

            // BOSS: Виконання механік ПЕРЕД атакою боса
            if ($isBoss && $locationMonster->hp_now > 0) {
                $this->bossMechanicsService->processMechanics(
                    $locationMonster,
                    $battle,
                    $player,
                    $roundLog
                );
            }

            // Атака монстра/боса (пропускаем если монстр оглушён)
            if ($locationMonster->hp_now > 0 && ! $monsterIsStunned) {
                // BOSS: Використовуємо спеціальну атаку для боса
                if ($isBoss) {
                    $this->monsterAttackService->executeBossAttack(
                        $player,
                        $locationMonster,
                        $battle,
                        $roundLog
                    );
                } else {
                    $this->monsterAttackService->execute($player, $locationMonster, $roundLog);
                }
            }

            if ($locationMonster->hp_now <= 0) {
                $this->attackService->handleMonsterDeath($player, $locationMonster, $attackedMonster, $roundLog);

                // BOSS: Додаткова обробка смерті боса
                if ($isBoss) {
                    $this->bossMechanicsService->handleBossDeath(
                        $locationMonster,
                        $battle,
                        $player,
                        $roundLog
                    );
                }
            }

            $player->save();

            if ($player->hp_now <= 0) {
                $fightDTO = $this->playerDeathService->handle(
                    $player,
                    $battle,
                    $battleRound,
                    $attackedPlayer,
                    $attackedMonster,
                    $roundLog
                );

                $this->finishService->checkAndFinish($battle, $user->currentLocation);

                return $fightDTO;
            }

            $user->save();
            $locationMonster->save();

            $this->attackService->checkLevelUp($player, $roundLog);

            $finishDTO = $this->finishService->checkAndFinish($battle, $user->currentLocation);

            $battleRound->action = $roundLog->getLog();
            $battleRound->save();

            // Зберігаємо метадані бою з босом
            if ($isBoss) {
                $this->saveBossMetadata($battle, $battleRound, $locationMonster);
            }

            return $fightDTO
                ->setBattle($finishDTO->battle ?? $battle)
                ->setBattleRound($battleRound)
                ->setAttackedMonster($attackedMonster)
                ->setPlayer($player->refresh());
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
