<?php

declare(strict_types=1);

namespace App\Modules\Structure\ClanBoss\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Battle\Application\Services\Battle\BattleOrchestrator;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Dungeon\Application\Services\ClanBossRewardRecorder;
use App\Modules\Dungeon\Application\Services\ClanBossService;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ClanBossController extends Controller
{
    public function __construct(
        private readonly ClanBossService $clanBossService,
        private readonly BattleOrchestrator $battleOrchestrator,
        private readonly ClanBossRewardRecorder $rewardRecorder,
    ) {}

    public function index(int $id): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $structure = $this->findLair($id);
        $structure->loadMissing('clanBossDungeon.stages');
        $dungeon = $structure->clanBossDungeon;

        $membership = $user->clanMembership;
        $clan = $membership?->clan;

        $run = $clan !== null && $dungeon !== null
            ? $this->clanBossService->findActiveRun($dungeon, (int) $clan->id)
            : null;

        $currentState = null;
        $stageStates = collect();
        $bossMonster = null;
        $myContribution = null;
        $myRunContribution = null;
        $rating = [];
        $hitsLeft = null;
        $personalAvailableAt = null;

        if ($run !== null) {
            $rating = $this->clanBossService->ratingForRun($run->id);
            $myRunContribution = collect($rating)->first(fn (array $row): bool => (int) $row['user']?->id === (int) $user->id);
            $stageStates = ClanBossRunStage::query()
                ->with(['stage', 'bossInstance.monster'])
                ->where('clan_boss_run_id', $run->id)
                ->get()
                ->keyBy('dungeon_stage_id');
            $currentState = $stageStates->get($run->current_stage_id);

            if ($currentState !== null) {
                $bossMonster = $currentState->bossInstance?->monster;
                $myContribution = $this->clanBossService->myContribution(
                    $run->id,
                    (int) $currentState->dungeon_stage_id,
                    $user->id,
                );

                $batchStatus = $this->clanBossService->playerAttackBatchStatus($run, $user);
                $hitsLeft = $batchStatus['hits_left'];
                $personalAvailableAt = $batchStatus['cooldown_until'];
            }
        }

        $stages = $dungeon !== null
            ? DungeonStage::query()
                ->where('dungeon_id', $dungeon->id)
                ->with('monsters.monster')
                ->orderBy('number')
                ->get()
            : collect();

        $canStartClanBossBattle = $membership !== null
            && ($membership->role->hasPermission(ClanPermission::START_CLAN_BOSS) || (int) $clan->owner_id === (int) $user->id);
        $canLeadOpen = $canStartClanBossBattle;
        $canAttack = $membership !== null;
        $onLocation = (int) $user->location_id === (int) $structure->location_id;
        $meetsLevel = $dungeon !== null
            && (int) ($user->player?->lvl ?? 0) >= (int) $dungeon->min_level;
        $canOpen = $canLeadOpen && $onLocation && $meetsLevel;

        return view('clan_boss::index', [
            'structure' => $structure,
            'dungeon' => $dungeon,
            'clan' => $clan,
            'run' => $run,
            'currentState' => $currentState,
            'stageStates' => $stageStates,
            'bossMonster' => $bossMonster,
            'myContribution' => $myContribution,
            'myRunContribution' => $myRunContribution,
            'rating' => $rating,
            'rewardReceipts' => $run !== null ? $this->rewardRecorder->history($run->id) : collect(),
            'hitsLeft' => $hitsLeft,
            'personalAvailableAt' => $personalAvailableAt,
            'stages' => $stages,
            'canOpen' => $canOpen,
            'canLeadOpen' => $canLeadOpen,
            'canAttack' => $canAttack,
            'meetsLevel' => $meetsLevel,
            'onLocation' => $onLocation,
        ]);
    }

    public function open(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $structure = $this->findLair($id);

        try {
            $this->clanBossService->openRun($structure, $user);
        } catch (RuntimeException $e) {
            return redirect()->route('clan_boss', ['id' => $id])->with('message', $e->getMessage());
        }

        return redirect()->route('clan_boss', ['id' => $id])->with('success', 'Поход открыт! Босс ждёт.');
    }

    public function attack(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $structure = $this->findLair($id);

        if ((int) $user->location_id !== (int) $structure->location_id) {
            return redirect()->route('clan_boss', ['id' => $id])
                ->with('message', 'Подойдите к логову, чтобы атаковать босса.');
        }

        $structure->loadMissing('clanBossDungeon');
        $dungeon = $structure->clanBossDungeon;
        $clanId = $user->clanMembership?->clan_id;

        if ($dungeon === null || $clanId === null) {
            return redirect()->route('clan_boss', ['id' => $id])
                ->with('message', 'Нет активного похода вашего клана.');
        }

        $run = $this->clanBossService->findActiveRun($dungeon, (int) $clanId);
        $instanceId = $run !== null
            ? ClanBossRunStage::query()
                ->where('clan_boss_run_id', $run->id)
                ->where('dungeon_stage_id', $run->current_stage_id)
                ->value('monster_on_location_id')
            : null;

        if ($run === null || $instanceId === null) {
            return redirect()->route('clan_boss', ['id' => $id])
                ->with('message', 'Нет активного похода вашего клана.');
        }

        $attackBatch = $this->clanBossService->playerAttackBatchStatus($run, $user);
        if ($attackBatch['hits_left'] < 1) {
            $message = $attackBatch['cooldown_until'] !== null
                ? sprintf(
                    'Пакет ударов восстановится через %s.',
                    format_game_duration((int) max(0, now()->diffInSeconds($attackBatch['cooldown_until'], false))),
                )
                : 'Вы использовали все удары в пакете. Дождитесь его восстановления.';

            return redirect()->route('clan_boss', ['id' => $id])->with('message', $message);
        }

        $user->loadMissing('currentLocation');
        $battle = $this->battleOrchestrator->handlePlayerAttack($user->currentLocation, (int) $instanceId);

        if ($battle === null) {
            return redirect()->route('clan_boss', ['id' => $id])
                ->with('message', 'Босс сейчас недоступен.');
        }

        return redirect()->route('fight.clan-boss', ['id' => $battle->id]);
    }

    private function findLair(int $id): Structure
    {
        $structure = Structure::query()->findOrFail($id);
        abort_unless($structure->isClanBoss(), 404);

        return $structure;
    }
}
