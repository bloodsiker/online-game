<?php

namespace App\Modules\Battle\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Battle\Application\DTOs\FightDTO;
use App\Modules\Battle\Application\Services\Battle\BattleOrchestrator;
use App\Modules\Battle\Application\Services\Battle\BattleService;
use App\Modules\Battle\Application\Services\Combat\FightOrchestrator;
use App\Modules\Battle\Application\UseCases\LeaveClanBossBattle;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Dungeon\Application\Services\ClanBossService;
use App\Modules\Dungeon\Application\UseCases\ExpireDungeonSession;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Prison\Application\Services\DetentionService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FightController extends Controller
{
    public function __construct(
        protected readonly BattleOrchestrator $battleOrchestrator,
        protected readonly BattleService $battleService,
        protected readonly FightOrchestrator $fightOrchestrator,
        protected readonly PlayerStatService $statService,
        protected readonly ExpireDungeonSession $expireDungeonSession,
        protected readonly LeaveClanBossBattle $leaveClanBossBattle,
        protected readonly ClanBossService $clanBossService,
    ) {}

    public function index($id)
    {
        $user = Auth::user();
        $player = $user->player;
        $battle = Battle::findOrFail($id);

        if ($this->clanBossDetailForBattle($battle) !== null) {
            return redirect()->route('fight.clan-boss', ['id' => $battle->id]);
        }

        if ($user->currentLocation !== $battle->location_id) {

        }

        $randomAttackedMonster = BattleDetail::with(['locationMonster.monster'])
            ->whereNotNull('location_monster_id')
            ->where(['battle_id' => $battle->id])
            ->where('status', 1)
            ->inRandomOrder()
            ->first();

        $lastRound = $battle->status->isFinish()
            ? BattleRound::with(['locationMonster.monster'])
                ->where('battle_id', $battle->id)
                ->orderByDesc('round_number')
                ->first()
            : null;

        $playerDecorator = $this->statService->resolve($player);

        $battle->loadMissing(['detailsWithMonsters.locationMonster.monster', 'detailsWithUsers.user']);
        $player->loadMissing(['user' => static fn ($query) => $query->without('player')]);

        $hitsLeft = $this->hitsLeftForClanBoss($randomAttackedMonster, $user);

        return view('battle::index', compact('battle', 'randomAttackedMonster', 'lastRound', 'player', 'playerDecorator', 'hitsLeft'));
    }

    public function clanBoss(int $id): View|RedirectResponse
    {
        $user = Auth::user();
        $battle = Battle::query()->findOrFail($id);
        $detail = $this->clanBossDetailForBattle($battle);
        abort_if($detail === null, 404);

        $run = $detail->locationMonster?->clanBossStageState?->run;
        $membership = $user->clanMembership;
        abort_unless($run !== null && (int) $membership?->clan_id === (int) $run->clan_id, 404);

        $dungeon = $run->dungeon;
        $lair = Structure::query()
            ->where('type', Structure::TYPE_CLAN_BOSS)
            ->where('dungeon_id', $dungeon->id)
            ->firstOrFail();

        return $this->renderClanBossBattle($battle, $user, $lair);
    }

    public function attack(int $id, int $monsterId, int $action)
    {
        if ($this->clanBossAttackContext($id, $monsterId) !== null) {
            return redirect()->route('fight.clan-boss.attack', [
                'id' => $id,
                'monsterId' => $monsterId,
                'action' => $action,
            ]);
        }

        return $this->performAttack($id, $monsterId, $action, false);
    }

    public function attackClanBoss(int $id, int $monsterId, int $action): View|RedirectResponse
    {
        $context = $this->clanBossAttackContext($id, $monsterId);
        abort_if($context === null, 404);

        $membership = Auth::user()->clanMembership;
        $run = $context['detail']->locationMonster?->clanBossStageState?->run;
        abort_unless($run !== null && (int) $membership?->clan_id === (int) $run->clan_id, 404);

        return $this->performAttack($id, $monsterId, $action, true);
    }

    private function performAttack(int $id, int $monsterId, int $action, bool $isClanBoss): View|RedirectResponse
    {
        $user = Auth::user();
        if (! Cache::add('cd:fight:'.$user->id, 1, 1)) {
            return redirect()->route($isClanBoss ? 'fight.clan-boss' : 'fight', ['id' => $id]);
        }
        if ($this->expireDungeonSession->execute($user)) {
            session()->flash('message', __('Время в подземелье истекло! Вы возвращены к входу.'));

            return redirect()->route('location');
        }

        try {
            $fightDTO = $this->fightOrchestrator->attack($id, $monsterId, $action);
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() !== 409) {
                throw $exception;
            }

            $context = $this->clanBossAttackContext($id, $monsterId);
            if ($context === null) {
                throw $exception;
            }

            $lairId = (int) $context['lair']->id;
            $message = $exception->getMessage() ?: __('Удары по боссу сейчас недоступны.');
            if ($this->clanBossService->hitsLeftForBattleDetail($context['detail'], $user) === 0) {
                try {
                    $lairId = $this->leaveClanBossBattle->execute($user, $id);
                    $message = __('Вы использовали все удары. ').$message;
                } catch (\RuntimeException) {
                    // The battle may have finished between the rejected attack
                    // and the safe-exit request; still return to the lair page.
                }
            }

            return redirect()->route('clan_boss', ['id' => $lairId])->with('message', $message);
        }

        $battle = $fightDTO->getBattle();
        $round = $fightDTO->getBattleRound();
        $player = $fightDTO->getPlayer();
        $playerDecorator = $this->statService->resolve($player);

        // Свіжий юзер для в'юхи (гроші/діаманти), без каскаду player+race.
        $player->loadMissing(['user' => static fn ($query) => $query->without('player')]);

        if ($fightDTO->isPlayerDead()) {
            $attackedMonster = $fightDTO->getAttackedMonster();

            return view('battle::death', compact('battle', 'player', 'playerDecorator', 'round', 'attackedMonster', 'fightDTO'));
        }

        // Учасники для side_panel одним заходом: інакше кожен моб тягне
        // monsters окремим запитом. На завершеному бою зв'язки вже
        // завантажені фініш-сервісом, loadMissing тоді нічого не робить.
        $battle->loadMissing(['detailsWithMonsters.locationMonster.monster', 'detailsWithUsers.user']);

        $randomAttackedMonster = BattleDetail::with(['locationMonster.monster'])
            ->whereNotNull('location_monster_id')
            ->where(['battle_id' => $battle->id, 'status' => 1])
            ->inRandomOrder()
            ->first();

        $hitsLeft = $this->hitsLeftForClanBoss($randomAttackedMonster, $user);

        if ($isClanBoss) {
            $context = $this->clanBossAttackContext($id, (int) ($randomAttackedMonster?->location_monster_id ?? $monsterId));
            if ($context === null) {
                return redirect()->route('fight.clan-boss', ['id' => $id]);
            }

            return $this->renderClanBossBattle($battle, $user, $context['lair'], $round, $fightDTO);
        }

        return view('battle::attack', compact('battle', 'round', 'randomAttackedMonster', 'player', 'playerDecorator', 'fightDTO', 'hitsLeft'));
    }

    public function attackMonster($id)
    {
        $user = Auth::user();
        if (! Cache::add('cd:fight:'.$user->id, 1, 1)) {
            return redirect()->route('location');
        }
        $player = $user->player;

        //        $battle = $this->battleOrchestrator->handlePlayerAttack($user->currentLocation, $id);
        $battle = $this->battleService->attackMonster($user->currentLocation, $id);

        $randomAttackedMonster = BattleDetail::with(['locationMonster.monster'])
            ->where(['location_monster_id' => $id])
            ->where('status', 1)
            ->first();

        $playerDecorator = $this->statService->resolve($player);

        $battle->loadMissing(['detailsWithMonsters.locationMonster.monster', 'detailsWithUsers.user']);
        $player->loadMissing(['user' => static fn ($query) => $query->without('player')]);

        $hitsLeft = $this->hitsLeftForClanBoss($randomAttackedMonster, $user);

        return view('battle::index', compact('battle', 'randomAttackedMonster', 'player', 'playerDecorator', 'hitsLeft'));
    }

    public function log(int $id): View
    {
        $battle = Battle::with(['location'])->findOrFail($id);
        $rounds = BattleRound::with(['user.player', 'locationMonster.monster', 'hits'])
            ->where('battle_id', $id)
            ->orderBy('round_number')
            ->get();

        return view('battle::log', compact('battle', 'rounds'));
    }

    public function leaveClanBoss(int $id): RedirectResponse
    {
        try {
            $structureId = $this->leaveClanBossBattle->execute(Auth::user(), $id);
        } catch (\RuntimeException $exception) {
            return redirect()->route('fight.clan-boss', ['id' => $id])->with('message', $exception->getMessage());
        }

        return redirect()->route('clan_boss', ['id' => $structureId])
            ->with('success', __('Вы вышли из боя. Оставшиеся удары сохранены; клановый поход продолжается.'));
    }

    public function runAway($id)
    {
        $user = Auth::user();
        if ($detentionMessage = app(DetentionService::class)->blockLeaving($user)) {
            return redirect()->route('fight', ['id' => $id])->with('message', $detentionMessage);
        }

        $battle = Battle::query()->findOrFail($id);
        if ($this->clanBossDetailForBattle($battle) !== null) {
            return redirect()->route('fight.clan-boss', ['id' => $id])
                ->with('message', __('Из боя с клановым боссом можно выйти безопасно через кнопку «Выйти из боя».'));
        }

        if (! Cache::add('cd:fight:'.$user->id, 1, 1)) {
            return redirect()->route('fight', ['id' => $id]);
        }
        $player = $user->player;
        $prevLocation = $user->prev_location_id;

        $chanceRun = mt_rand(0, 100);

        if ($battle->status === 1) {
            if ($chanceRun > 50) {
                $usersInBattle = BattleDetail::where('user_id', '!=', $user->id)->whereNotNull('user_id')->count();
                if ($usersInBattle > 0) {
                    $battleRound = new BattleRound;
                    $battleRound->battle_id = $battle->id;
                    $battleRound->round_number = ++$battle->rounds;
                    $battleRound->user_id = $user->id;
                    $battleRound->action = '<p>Вы бежали на восток...</p>';
                    $battleRound->save();

                    BattleDetail::where('user_id', $user->id)->delete();
                } else {
                    $battle->status = 0;
                    $battle->save();

                    $battleRound = new BattleRound;
                    $battleRound->battle_id = $battle->id;
                    $battleRound->round_number = ++$battle->rounds;
                    $battleRound->user_id = $user->id;
                    $battleRound->action = '<p>Вы бежали на восток...</p>';
                    $battleRound->save();
                }
            } else {
                $randomAttackedMonster = BattleDetail::with(['locationMonster.monster'])
                    ->whereNotNull('location_monster_id')
                    ->where(['battle_id' => $battle->id])
                    ->where('status', 1)
                    ->inRandomOrder()
                    ->first();

                $round = new BattleRound;
                $round->battle_id = $battle->id;
                $round->round_number = $battle->rounds;
                $round->user_id = $user->id;
                $round->location_monster_id = $randomAttackedMonster->location_monster_id;
                $round->action = '<p>Вы попытались бежать... не получилось.</p>';
                $round->save();

                $battle->rounds += 1;
                $battle->save();

                $fightDTO = (new FightDTO)
                    ->setBattle($battle)
                    ->setBattleRound($round)
                    ->setPlayer($player);

                $playerDecorator = $this->statService->resolve($player);

                return view('battle::attack', compact('battle', 'round', 'randomAttackedMonster', 'player', 'playerDecorator', 'fightDTO'));
            }
        }

        $user->prev_location_id = $user->location_id;
        $user->location_id = $prevLocation;
        $user->save();

        return view('battle::run_away');
    }

    private function hitsLeftForClanBoss(?BattleDetail $randomAttackedMonster, User $user): ?int
    {
        return $this->clanBossService->hitsLeftForBattleDetail($randomAttackedMonster, $user);
    }

    private function renderClanBossBattle(
        Battle $battle,
        User $user,
        Structure $lair,
        ?BattleRound $round = null,
        ?FightDTO $fightDTO = null,
    ): View {
        $player = $user->player;
        $playerDecorator = $this->statService->resolve($player);
        $player->loadMissing(['user' => static fn ($query) => $query->without('player')]);
        $round?->loadMissing('locationMonster.monster');

        $battle->loadMissing(['detailsWithMonsters.locationMonster.monster', 'detailsWithUsers.user']);

        $randomAttackedMonster = BattleDetail::query()
            ->with(['locationMonster.monster'])
            ->whereNotNull('location_monster_id')
            ->where('battle_id', $battle->id)
            ->where('status', 1)
            ->inRandomOrder()
            ->first();

        $lastRound = $round ?? ($battle->status->isFinish()
            ? BattleRound::query()
                ->with(['locationMonster.monster'])
                ->where('battle_id', $battle->id)
                ->orderByDesc('round_number')
                ->first()
            : null);
        $lastRound?->loadMissing('locationMonster.monster');
        $hitsLeft = $this->hitsLeftForClanBoss($randomAttackedMonster, $user);

        return view('battle::clan_boss', compact(
            'battle', 'round', 'lastRound', 'randomAttackedMonster', 'player', 'playerDecorator',
            'fightDTO', 'hitsLeft', 'lair',
        ));
    }

    private function clanBossDetailForBattle(Battle $battle): ?BattleDetail
    {
        return BattleDetail::query()
            ->with(['locationMonster.clanBossStageState.run.dungeon'])
            ->where('battle_id', $battle->id)
            ->whereNotNull('location_monster_id')
            ->whereHas('locationMonster', static fn ($query) => $query->whereNotNull('clan_boss_stage_state_id'))
            ->first();
    }

    /** @return array{detail: BattleDetail, lair: Structure}|null */
    private function clanBossAttackContext(int $battleId, int $monsterId): ?array
    {
        $detail = BattleDetail::query()
            ->with('locationMonster.clanBossStageState.run')
            ->where('battle_id', $battleId)
            ->where('location_monster_id', $monsterId)
            ->first();

        $dungeonId = $detail?->locationMonster?->clanBossStageState?->run?->dungeon_id;
        if ($dungeonId === null) {
            return null;
        }

        $lair = Structure::query()
            ->where('type', Structure::TYPE_CLAN_BOSS)
            ->where('dungeon_id', $dungeonId)
            ->first();

        return $lair === null ? null : ['detail' => $detail, 'lair' => $lair];
    }
}
