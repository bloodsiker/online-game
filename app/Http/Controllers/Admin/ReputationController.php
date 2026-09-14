<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Reputation\Domain\Enums\DivineFavorType;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopItem;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTierQuest;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models\ReputationExchange;
use App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models\ReputationGambleOption;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReputationController extends Controller
{
    private readonly AdminImageStorage $imageStorage;

    public function __construct(?AdminImageStorage $imageStorage = null)
    {
        $this->imageStorage = $imageStorage ?? new AdminImageStorage;
    }

    public function list()
    {
        $reputations = Reputation::withCount(['tiers', 'shopItems'])->orderByDesc('id')->get();

        return view('admin.reputation.list', compact('reputations'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $reputation = new Reputation;
            $this->fillReputation($reputation, $request);
            $reputation->save();

            return redirect()->route('admin.reputation.info', $reputation->id)
                ->with('success', 'Репутация создана.');
        }

        return view('admin.reputation.create');
    }

    public function info(Request $request, Reputation $reputation): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillReputation($reputation, $request);
            $reputation->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $reputation->load(['npc', 'tiers.quests.quest', 'tiers.featQuest', 'shopItems.item', 'gambleOptions.shareItem', 'exchangeItems.shareItem', 'elixirItem', 'favorMarkerEffect']);
        $exchangeStructure = $reputation->npc_id
            ? Structure::where('npc_id', $reputation->npc_id)->where('type', Structure::TYPE_REPUTATION_EXCHANGE)->first()
            : null;
        $favorTypes = DivineFavorType::cases();
        $effects = Effect::orderBy('name')->get(['id', 'name']);

        return view('admin.reputation.info', compact('reputation', 'exchangeStructure', 'favorTypes', 'effects'));
    }

    public function addTier(Request $request, Reputation $reputation): RedirectResponse
    {
        ReputationTier::create([
            'reputation_id' => $reputation->id,
            ...$this->tierData($request),
        ]);

        return redirect()->back()->with('success', 'Уровень добавлен.');
    }

    public function updateTier(Request $request, Reputation $reputation, ReputationTier $tier): RedirectResponse
    {
        $tier->update($this->tierData($request));

        return redirect()->back()->with('success', 'Уровень обновлён.');
    }

    public function deleteTier(Reputation $reputation, ReputationTier $tier): RedirectResponse
    {
        $tier->quests()->delete();
        $tier->delete();

        return redirect()->back()->with('success', 'Уровень удалён.');
    }

    public function addTierQuest(Request $request, Reputation $reputation, ReputationTier $tier): RedirectResponse
    {
        ReputationTierQuest::firstOrCreate([
            'tier_id' => $tier->id,
            'quest_id' => (int) $request->input('quest_id'),
        ]);

        return redirect()->back()->with('success', 'Квест добавлен к уровню.');
    }

    public function deleteTierQuest(Reputation $reputation, ReputationTier $tier, ReputationTierQuest $tierQuest): RedirectResponse
    {
        $tierQuest->delete();

        return redirect()->back()->with('success', 'Квест удалён из уровня.');
    }

    public function addShopItem(Request $request, Reputation $reputation): RedirectResponse
    {
        ReputationShopItem::create([
            'reputation_id' => $reputation->id,
            'share_item_id' => (int) $request->input('share_item_id'),
            'price' => (int) $request->input('price', 0),
            'diamond' => (int) $request->input('diamond', 0),
            'min_points' => (int) $request->input('min_points', 0),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->back()->with('success', 'Предмет добавлен в магазин.');
    }

    public function deleteShopItem(Reputation $reputation, ReputationShopItem $shopItem): RedirectResponse
    {
        $shopItem->delete();

        return redirect()->back()->with('success', 'Предмет удалён из магазина.');
    }

    public function addExchangeItem(Request $request, Reputation $reputation): RedirectResponse
    {
        $structure = Structure::where('npc_id', $reputation->npc_id)->where('type', Structure::TYPE_REPUTATION_EXCHANGE)->first();
        if ($structure === null) {
            return redirect()->back()->with('error', 'У NPC этой репутации ещё нет здания «Обмен на репутацию» (создайте Structure с type=reputation_exchange на этого NPC).');
        }

        ReputationExchange::create([
            'structure_id' => $structure->id,
            'reputation_id' => $reputation->id,
            'share_item_id' => (int) $request->input('share_item_id'),
            'points' => (int) $request->input('points', 5),
            'min_reputation' => (int) $request->input('min_reputation', 0),
            'max_reputation' => (int) $request->input('max_reputation', 999999),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->back()->with('success', 'Предмет обмена добавлен.');
    }

    public function deleteExchangeItem(Reputation $reputation, ReputationExchange $exchangeItem): RedirectResponse
    {
        $exchangeItem->delete();

        return redirect()->back()->with('success', 'Предмет обмена удалён.');
    }

    public function addGambleOption(Request $request, Reputation $reputation): RedirectResponse
    {
        ReputationGambleOption::create([
            'reputation_id' => $reputation->id,
            'share_item_id' => (int) $request->input('share_item_id'),
            'resource_cost' => (int) $request->input('resource_cost', 0),
            'success_chance' => min(100, max(1, (int) $request->input('success_chance', 100))),
            'reward_points' => (int) $request->input('reward_points', 0),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->back()->with('success', 'Вариант обмена добавлен.');
    }

    public function deleteGambleOption(Reputation $reputation, ReputationGambleOption $gambleOption): RedirectResponse
    {
        $gambleOption->delete();

        return redirect()->back()->with('success', 'Вариант обмена удалён.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function tierData(Request $request): array
    {
        $request->validate([
            'medal_image' => ['nullable', 'image', 'max:4096'],
            'feat_medal_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $data = [
            'min_points' => (int) $request->input('min_points', 0),
            // Пустое значение = открытый верхний тир (без потолка)
            'max_points' => $request->filled('max_points') ? (int) $request->input('max_points') : null,
            'medal_name' => $request->input('medal_name') ?: null,
            'medal_icon' => $request->input('medal_icon') ?: null,
            // Подвиг: квест (или финальный квест цепочки), без которого медаль не выдаётся
            'feat_quest_id' => $request->input('feat_quest_id') ?: null,
            'feat_description' => $request->input('feat_description') ?: null,
            'feat_medal_name' => $request->input('feat_medal_name') ?: null,
            'feat_medal_icon' => $request->input('feat_medal_icon') ?: null,
            'favor_percent' => $request->filled('favor_percent') ? (int) $request->input('favor_percent') : null,
            'feat_favor_percent' => $request->filled('feat_favor_percent') ? (int) $request->input('feat_favor_percent') : null,
            'favor_duration_seconds' => $request->filled('favor_duration_seconds') ? (int) $request->input('favor_duration_seconds') : null,
        ];

        if ($request->hasFile('medal_image')) {
            $data['medal_icon'] = $this->imageStorage->storeOnPublicDisk(
                $request->file('medal_image'),
                'reputations/medals',
            );
        }

        if ($request->hasFile('feat_medal_image')) {
            $data['feat_medal_icon'] = $this->imageStorage->storeOnPublicDisk(
                $request->file('feat_medal_image'),
                'reputations/medals',
            );
        }

        return $data;
    }

    private function fillReputation(Reputation $reputation, Request $request): void
    {
        $reputation->name = $request->input('name');
        $reputation->description = $request->input('description');
        $reputation->npc_id = $request->input('npc_id') ?: null;
        $reputation->icon = $request->input('icon');
        $reputation->favor_effect_type = $request->input('favor_effect_type') ?: null;
        $reputation->favor_proc_chance = $request->filled('favor_proc_chance') ? (int) $request->input('favor_proc_chance') : null;
        $reputation->elixir_share_item_id = $request->input('elixir_share_item_id') ?: null;
        $reputation->favor_marker_effect_id = $request->input('favor_marker_effect_id') ?: null;
    }
}
