<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Infrastructure\Persistence\Models\LoginHistory;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\Moderation\Domain\Models\UserCommunicationMute;
use App\Modules\Player\Domain\Services\PlayerLevelUpService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Prison\Application\Services\DetentionService;
use App\Modules\Structure\Prison\Infrastructure\Persistence\Models\PlayerDetention;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlayerController extends Controller
{
    public function index()
    {
        $players = Player::with('user', 'race')->orderByDesc('id')->get();

        return view('admin.player.list', compact('players'));
    }

    public function info(
        Request $request,
        Player $player,
        ReputationService $reputationService,
        PlayerLevelUpService $levelUpService,
    ): mixed {
        if ($request->isMethod('POST')) {
            $validated = $request->validate([
                'experience_multiplier' => ['required', 'numeric', 'min:0', 'max:9999.9999'],
            ]);

            $player->lvl = (int) $request->input('lvl', 1);
            $player->exp = (int) $request->input('exp', 0);
            $player->experience_multiplier = (float) $validated['experience_multiplier'];
            $player->strength = (float) $request->input('strength');
            $player->agility = (float) $request->input('agility');
            $player->intuition = (float) $request->input('intuition');
            $player->wisdom = (float) $request->input('wisdom');
            $player->intelligence = (float) $request->input('intelligence');
            $player->min_dmg = (float) $request->input('min_dmg');
            $player->max_dmg = (float) $request->input('max_dmg');
            $player->free_stats = (int) $request->input('free_stats');
            $player->save();

            $player->user->money = (int) $request->input('money');
            $player->user->diamond = (int) $request->input('diamond');
            $player->user->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        return view('admin.player.info', $this->pageData($player));
    }

    public function skills(Player $player): mixed
    {
        return view('admin.player.skills', $this->pageData($player));
    }

    public function reputations(Player $player): mixed
    {
        return view('admin.player.reputations', $this->pageData($player));
    }

    public function mutes(Player $player): mixed
    {
        return view('admin.player.mutes', $this->pageData($player));
    }

    public function logins(Player $player): mixed
    {
        return view('admin.player.logins', $this->pageData($player));
    }

    public function detention(Player $player): mixed
    {
        return view('admin.player.detention', $this->pageData($player));
    }

    /** @return array<string, mixed> */
    private function pageData(Player $player): array
    {
        $reputationService = app(ReputationService::class);
        $levelUpService = app(PlayerLevelUpService::class);

        $player->load(['user.currentLocation.map', 'race', 'skills.skill', 'reputations.reputation.tiers']);
        $maxPlayerLevel = $levelUpService->maxLevel();

        $playerReputations = $player->reputations
            ->filter(fn ($playerReputation) => $playerReputation->reputation !== null)
            ->sortBy(fn ($playerReputation) => $playerReputation->reputation->name)
            ->map(fn ($playerReputation) => [
                'record' => $playerReputation,
                'currentTier' => $reputationService->getCurrentTier(
                    $playerReputation->reputation,
                    $playerReputation->points,
                ),
            ])
            ->values();

        $backpackCount = Backpack::query()->where('user_id', $player->user_id)->count();

        $communicationMutes = UserCommunicationMute::query()
            ->where('user_id', $player->user_id)
            ->with(['imposedBy', 'revokedBy'])
            ->latest()
            ->limit(50)
            ->get();
        $activeCommunicationMutes = UserCommunicationMute::query()
            ->where('user_id', $player->user_id)
            ->active()
            ->get()
            ->keyBy(fn (UserCommunicationMute $mute): string => $mute->scope->value);
        $prisons = Structure::query()
            ->where('type', Structure::TYPE_PRISON)
            ->whereNotNull('location_id')
            ->orderBy('name->ru')
            ->get();
        $detentionHistory = PlayerDetention::query()
            ->with(['prison', 'imposedBy'])
            ->where('user_id', $player->user_id)
            ->latest('starts_at')
            ->limit(50)
            ->get();
        $activeDetention = $detentionHistory->first(fn (PlayerDetention $record): bool => $record->isActive());

        $loginHistory = LoginHistory::query()
            ->where('user_id', $player->user_id)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $secretKnowledgePoints = (int) \DB::table('player_secret_knowledge')->where('player_id', $player->id)->value('points');
        $studiedManuals = \DB::table('player_studied_boss_manuals as s')
            ->join('boss_knowledge_manuals as m', 'm.id', '=', 's.boss_knowledge_manual_id')
            ->join('monsters as mo', 'mo.id', '=', 'm.monster_id')
            ->where('s.player_id', $player->id)
            ->orderBy('m.required_knowledge')
            ->get(['mo.name as boss_name', 'm.required_knowledge', 's.studied_at'])
            ->map(function (object $row): object {
                $names = json_decode((string) $row->boss_name, true);
                $row->boss_name = is_array($names) ? ($names['ru'] ?? reset($names)) : (string) $row->boss_name;

                return $row;
            });

        $nav = [
            'backpack' => $backpackCount,
            'skills' => $player->skills->count(),
            'reputations' => $playerReputations->count(),
            'quests' => \DB::table('quest_players')->where('player_id', $player->id)->where('status', 'in_progress')->count(),
            'mutes' => $activeCommunicationMutes->count(),
            'logins' => $loginHistory->count(),
            'detention' => $activeDetention !== null,
        ];

        return compact(
            'secretKnowledgePoints',
            'studiedManuals',
            'loginHistory',
            'player',
            'backpackCount',
            'playerReputations',
            'maxPlayerLevel',
            'communicationMutes',
            'activeCommunicationMutes',
            'prisons',
            'detentionHistory',
            'activeDetention',
            'nav',
        );
    }

    public function backpack(Player $player): mixed
    {
        $backpack = Backpack::with(['item.itemInfo', 'item.treasureHunt'])
            ->where('user_id', $player->user_id)
            ->orderBy('equipped', 'desc')
            ->orderBy('id')
            ->get();

        return view('admin.player.backpack', ['player' => $player, 'backpack' => $backpack, 'nav' => $this->pageData($player)['nav']]);
    }

    public function quests(Player $player): mixed
    {
        $ru = static function (mixed $value): string {
            $decoded = is_string($value) ? json_decode($value, true) : $value;

            return is_array($decoded) ? (string) ($decoded['ru'] ?? reset($decoded) ?: '') : (string) $value;
        };

        $active = \DB::table('quest_players as qp')
            ->join('quests as q', 'q.id', '=', 'qp.quest_id')
            ->leftJoin('quest_stages as st', 'st.id', '=', 'qp.current_stage_id')
            ->where('qp.player_id', $player->id)
            ->where('qp.status', 'in_progress')
            ->orderByDesc('qp.updated_at')
            ->get(['qp.id', 'qp.quest_id', 'qp.current_stage_id', 'qp.current_stage_started_at', 'qp.created_at', 'q.title as quest_title', 'q.type', 'st.title as stage_title', 'st.order as stage_order']);

        $progress = \DB::table('quest_player_objectives')
            ->whereIn('quest_player_id', $active->pluck('id'))
            ->pluck('amount', \DB::raw("CONCAT(quest_player_id, '-', quest_objective_id)"));

        $quests = $active->map(function (object $row) use ($ru, $progress): object {
            $row->quest_title = $ru($row->quest_title);
            $row->stage_title = $ru($row->stage_title);
            $row->objectives = \DB::table('quest_objectives')
                ->where('quest_id', $row->quest_id)
                ->where('stage_id', $row->current_stage_id)
                ->get()
                ->map(function (object $objective) use ($ru, $progress, $row): object {
                    $objective->text = $ru($objective->description);
                    $objective->current = (int) ($progress[$row->id.'-'.$objective->id] ?? 0);

                    return $objective;
                });

            return $row;
        });

        $completedCount = \DB::table('quest_players')->where('player_id', $player->id)->where('status', 'completed')->count();

        return view('admin.player.quests', ['player' => $player, 'quests' => $quests, 'completedCount' => $completedCount, 'nav' => $this->pageData($player)['nav']]);
    }

    public function levelUp(Request $request, Player $player, PlayerLevelUpService $levelUpService): RedirectResponse
    {
        $validated = $request->validate([
            'target_level' => [
                'required',
                'integer',
                'min:'.($player->lvl + 1),
                'max:'.$levelUpService->maxLevel(),
            ],
        ]);

        $previousLevel = $player->lvl;
        $leveledPlayer = $levelUpService->raiseToLevel($player, (int) $validated['target_level']);

        return redirect()->back()->with(
            'success',
            "Уровень игрока повышен с {$previousLevel} до {$leveledPlayer->lvl}. Характеристики начислены за каждый уровень.",
        );
    }

    public function backpackAdd(Request $request, Player $player, BackpackService $backpackService): RedirectResponse
    {
        $shareItem = ShareItem::findOrFail((int) $request->input('share_item_id'));
        $count = max(1, (int) $request->input('count', 1));

        $backpackService->giveItemsByShareItem($player->user, $shareItem, $count);

        return redirect()->back()->with('success', 'Предмет добавлен в рюкзак.');
    }

    public function backpackDelete(Player $player, Backpack $backpack): RedirectResponse
    {
        $backpack->item?->delete();
        $backpack->delete();

        return redirect()->back()->with('success', 'Предмет удалён из рюкзака.');
    }

    public function mute(
        Request $request,
        Player $player,
        CommunicationMuteService $communicationMuteService,
    ): RedirectResponse {
        $validated = $request->validate([
            'scope' => ['required', Rule::enum(CommunicationScope::class)],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:525600'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $scope = CommunicationScope::from($validated['scope']);
        $mute = $communicationMuteService->mute(
            $player->user,
            $request->user(),
            $scope,
            (int) $validated['duration_minutes'],
            $validated['reason'] ?? null,
        );

        return redirect()->back()->with(
            'success',
            sprintf('%s заблокирован до %s.', $scope->label(), $mute->expires_at->format('d.m.Y H:i')),
        );
    }

    public function revokeMute(
        Request $request,
        Player $player,
        UserCommunicationMute $mute,
        CommunicationMuteService $communicationMuteService,
    ): RedirectResponse {
        abort_unless((int) $mute->user_id === (int) $player->user_id, 404);

        $communicationMuteService->revoke($mute, $request->user());

        return redirect()->back()->with('success', 'Молчание снято досрочно.');
    }

    public function detain(
        Request $request,
        Player $player,
        DetentionService $detentionService,
    ): RedirectResponse {
        $validated = $request->validate([
            'prison_structure_id' => ['required', 'integer', Rule::exists('structures', 'id')->where('type', Structure::TYPE_PRISON)->whereNotNull('location_id')],
            'indefinite' => ['nullable', 'boolean'],
            'duration_minutes' => ['exclude_if:indefinite,1,true', 'required', 'integer', 'min:1', 'max:525600'],
            'reason' => ['required', 'string', 'max:1000'],
            'fine_money' => ['nullable', 'integer', 'min:0', 'max:1000000000000'],
            'fine_diamond' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000000'],
        ]);

        $prison = Structure::query()->findOrFail((int) $validated['prison_structure_id']);
        $record = $detentionService->impose(
            $player->user,
            $prison,
            $request->user(),
            $request->boolean('indefinite') ? null : (int) $validated['duration_minutes'],
            $validated['reason'],
            (int) ($validated['fine_money'] ?? 0),
            (float) ($validated['fine_diamond'] ?? 0),
        );

        return redirect()->back()->with('success', $record->isIndefinite()
            ? 'Игрок помещён в Темницу бессрочно.'
            : 'Игрок помещён в Темницу до '.$record->releaseLabel().'.');
    }

    public function releaseDetention(Player $player, DetentionService $detentionService): RedirectResponse
    {
        return redirect()->back()->with('success', $detentionService->releaseByAdmin($player->user)
            ? 'Игрок освобождён из Темницы.'
            : 'Игрок не находится в Темнице.');
    }
}
