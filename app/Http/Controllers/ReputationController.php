<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Reputation\Reputation;
use App\Models\Reputation\ReputationShopItem;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ReputationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReputationController extends Controller
{
    public function __construct(
        private readonly ReputationService $reputationService,
        private readonly BackpackService $backpackService,
    ) {}

    public function list(): View
    {
        $user = Auth::user();
        $player = $user->player;

        $reputations = \App\Models\Reputation\Reputation::with('tiers', 'npc')->get();

        $playerReputations = $reputations->map(function ($reputation) use ($player) {
            $pr = $this->reputationService->getOrCreate($player, $reputation);
            $currentTier = $this->reputationService->getCurrentTier($reputation, $pr->points);
            $nextTier = $reputation->tiers->where('min_points', '>', $pr->points)->sortBy('min_points')->first();

            return [
                'reputation' => $reputation,
                'pr' => $pr,
                'currentTier' => $currentTier,
                'nextTier' => $nextTier,
            ];
        });

        $group = 'reputation';

        return view('reputation.list', compact('playerReputations', 'group'));
    }

    public function index(int $id): View
    {
        $reputation = Reputation::with('tiers.quests.quest', 'npc')->findOrFail($id);
        $user = Auth::user();
        $player = $user->player;

        $pr = $this->reputationService->getOrCreate($player, $reputation);
        $currentTier = $this->reputationService->getCurrentTier($reputation, $pr->points);
        $canTake = $this->reputationService->canTakeQuest($player, $reputation);
        $activeQuest = $this->reputationService->getActiveQuest($player, $reputation);
        $cooldownDiff = $this->reputationService->getCooldownDiff($player, $reputation);
        $earnedMedals = $this->reputationService->getEarnedMedals($reputation, $pr->points);

        $progressMap = [];
        if ($activeQuest) {
            foreach ($activeQuest->objectives as $po) {
                $progressMap[$po->quest_objective_id] = $po->amount;
            }
        }

        $message = session('rep_error') ?? session('rep_success');
        $messageType = session()->has('rep_success') ? 'success' : 'error';

        $group = 'reputation';

        return view('reputation.index', compact(
            'reputation', 'pr', 'currentTier', 'canTake', 'activeQuest',
            'cooldownDiff', 'earnedMedals', 'progressMap', 'message', 'messageType', 'player', 'group'
        ));
    }

    public function take(int $id, Request $request): RedirectResponse
    {
        $reputation = Reputation::with('tiers.quests.quest')->findOrFail($id);
        $user = Auth::user();
        $player = $user->player;

        if (! $this->reputationService->canTakeQuest($player, $reputation)) {
            return redirect()->route('reputation.index', $id)
                ->with('rep_error', 'Вы не можете взять новый квест прямо сейчас.');
        }

        $questPlayer = $this->reputationService->assignQuest($player, $reputation);

        if (! $questPlayer) {
            return redirect()->route('reputation.index', $id)
                ->with('rep_error', 'Нет доступных заданий для вашего уровня репутации.');
        }

        session()->forget('rep_offer_'.$player->id.'_'.$id);

        return redirect()->route('reputation.index', $id)
            ->with('rep_success', 'Задание получено!');
    }

    public function decline(int $id, Request $request): RedirectResponse
    {
        $user = Auth::user();
        $player = $user->player;

        $reputation = Reputation::findOrFail($id);
        $pr = $this->reputationService->getOrCreate($player, $reputation);
        $pr->update(['last_completed_at' => now()]);

        session()->forget('rep_offer_'.$player->id.'_'.$id);

        return redirect()->route('npc', $request->integer('npc'));
    }

    public function shop(int $id): View|RedirectResponse
    {
        $reputation = Reputation::with('shopItems.item', 'shopItems.requirements.item', 'npc')->findOrFail($id);
        $user = Auth::user();
        $player = $user->player;

        if (! $this->isAtNpcLocation($user, $reputation)) {
            return redirect()->route('reputation.list')
                ->with('rep_error', 'Магазин доступен только находясь рядом с НПС.');
        }

        $pr = $this->reputationService->getOrCreate($player, $reputation);

        $message = session('shop_error') ?? session('shop_success');
        $messageType = session()->has('shop_success') ? 'success' : 'error';

        return view('reputation.shop', compact('reputation', 'pr', 'message', 'messageType', 'player'));
    }

    public function buy(int $id, int $itemId, Request $request): RedirectResponse
    {
        $reputation = Reputation::with('npc')->findOrFail($id);
        $user = Auth::user();
        $player = $user->player;

        if (! $this->isAtNpcLocation($user, $reputation)) {
            return redirect()->route('reputation.list')
                ->with('rep_error', 'Магазин доступен только находясь рядом с НПС.');
        }

        $shopItem = ReputationShopItem::where('reputation_id', $reputation->id)
            ->with('item', 'requirements.item')
            ->findOrFail($itemId);

        $pr = $this->reputationService->getOrCreate($player, $reputation);

        if ($pr->points < $shopItem->min_points) {
            return redirect()->route('reputation.shop', $id)
                ->with('shop_error', 'Недостаточно очков репутации.');
        }

        if ($shopItem->price > 0 && $user->money < $shopItem->price) {
            return redirect()->route('reputation.shop', $id)
                ->with('shop_error', 'Недостаточно монет.');
        }

        if ($shopItem->diamond > 0 && $user->diamond < $shopItem->diamond) {
            return redirect()->route('reputation.shop', $id)
                ->with('shop_error', 'Недостаточно кристаллов.');
        }

        foreach ($shopItem->requirements as $req) {
            if (! $this->backpackService->hasItemByShareItem($user, $req->item, $req->quantity)) {
                return redirect()->route('reputation.shop', $id)
                    ->with('shop_error', "Нет нужного предмета: {$req->item->name}.");
            }
        }

        DB::transaction(function () use ($user, $shopItem) {
            if ($shopItem->price > 0) {
                $user->decrement('money', $shopItem->price);
            }
            if ($shopItem->diamond > 0) {
                $user->decrement('diamond', $shopItem->diamond);
            }

            foreach ($shopItem->requirements as $req) {
                $this->backpackService->removeItemByShareItem($user, $req->item, $req->quantity);
            }

            $this->backpackService->addItemByShareItem($user, $shopItem->item, 1);
        });

        return redirect()->route('reputation.shop', $id)
            ->with('shop_success', "Товар «{$shopItem->item->name}» куплен!");
    }

    private function isAtNpcLocation(User $user, Reputation $reputation): bool
    {
        if (! $reputation->npc || ! $reputation->npc->location_id) {
            return true;
        }

        return (int) $user->currentLocation?->id === (int) $reputation->npc->location_id;
    }
}
