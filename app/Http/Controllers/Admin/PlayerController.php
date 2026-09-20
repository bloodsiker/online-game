<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\Moderation\Domain\Models\UserCommunicationMute;
use App\Modules\Player\Domain\Services\PlayerLevelUpService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
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
            $player->hp_now = (int) $request->input('hp_now');
            $player->hp_max = (int) $request->input('hp_max');
            $player->mp_now = (int) $request->input('mp_now');
            $player->mp_max = (int) $request->input('mp_max');
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

        $player->load(['user', 'race', 'skills.skill', 'reputations.reputation.tiers']);
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

        $backpack = Backpack::with('item.itemInfo')
            ->where('user_id', $player->user_id)
            ->orderBy('equipped', 'desc')
            ->orderBy('id')
            ->get();

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

        return view('admin.player.info', compact(
            'player',
            'backpack',
            'playerReputations',
            'maxPlayerLevel',
            'communicationMutes',
            'activeCommunicationMutes',
        ));
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
}
