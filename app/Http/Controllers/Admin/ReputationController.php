<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopItem;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTierQuest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReputationController extends Controller
{
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

        $reputation->load(['npc', 'tiers.quests.quest', 'tiers.featQuest', 'shopItems.item']);

        return view('admin.reputation.info', compact('reputation'));
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
        ];

        if ($request->hasFile('medal_image')) {
            $data['medal_icon'] = $this->storeMedalImage($request->file('medal_image'));
        }

        if ($request->hasFile('feat_medal_image')) {
            $data['feat_medal_icon'] = $this->storeMedalImage($request->file('feat_medal_image'));
        }

        return $data;
    }

    private function storeMedalImage(UploadedFile $image): string
    {
        $directory = public_path('img/reputation/uploaded');
        File::ensureDirectoryExists($directory);

        $filename = Str::uuid()->toString().'.'.$image->extension();
        $image->move($directory, $filename);

        return 'img/reputation/uploaded/'.$filename;
    }

    private function fillReputation(Reputation $reputation, Request $request): void
    {
        $reputation->name = $request->input('name');
        $reputation->description = $request->input('description');
        $reputation->npc_id = $request->input('npc_id') ?: null;
        $reputation->icon = $request->input('icon');
    }
}
