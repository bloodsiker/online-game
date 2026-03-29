<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RuneRarity;
use App\Enums\ShareItemSlot;
use App\Enums\ShareItemType;
use App\Enums\UpgradeScrollType;
use App\Http\Controllers\Controller;
use App\Models\Share\ShareItem;
use App\Models\Share\ShareRecipe;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function list(): View
    {
        $listItems = ShareItem::orderByDesc('id')->get();

        return view('admin.item.list', compact('listItems'));
    }

    public function create(): View
    {
        $skills = Skill::orderBy('name')->get();

        return view('admin.item.create', compact('skills'));
    }

    public function store(Request $request): RedirectResponse
    {
        $item = new ShareItem;
        $this->fillItem($item, $request);
        $item->save();

        if ($item->type === ShareItemType::RECIPE) {
            ShareRecipe::firstOrCreate(['share_item_id' => $item->id], [
                'kraft_item_id' => null,
                'percent'       => 100,
            ]);
        }

        return redirect()->route('admin.item.info', ['item' => $item->id])
            ->with('success', 'Предмет создан.');
    }

    public function info(Request $request, ShareItem $item): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillItem($item, $request);
            $item->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $item->load(['recipe', 'recipe.items', 'recipe.kraftItem']);

        $skills = Skill::orderBy('name')->get();

        return view('admin.item.info', compact('item', 'skills'));
    }

    public function updateRecipe(Request $request, ShareRecipe $recipe): RedirectResponse
    {
        $recipe->kraft_item_id = $request->filled('kraft_item_id') ? (int) $request->input('kraft_item_id') : null;
        $recipe->percent       = (int) $request->input('percent', 100);
        $recipe->save();

        return redirect()->back()->with('success', 'Рецепт обновлён.');
    }

    public function addItemToRecipe(Request $request, ShareRecipe $recipe): RedirectResponse
    {
        $recipe->items()->attach(
            (int) $request->input('share_item_id'),
            ['count' => (int) $request->input('count', 1)]
        );

        return redirect()->back()->with('success', 'Ресурс добавлен.');
    }

    public function deleteItemInRecipe(Request $request, ShareRecipe $recipe, ShareItem $item): RedirectResponse
    {
        $recipe->items()->detach($item->id);

        return redirect()->back()->with('success', 'Ресурс удалён.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillItem(ShareItem $item, Request $request): void
    {
        $type = ShareItemType::from($request->input('type'));

        $item->name          = $request->input('name');
        $item->type          = $type;
        $item->description   = $request->input('description');
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            $file->move(public_path('img/resource'), $filename);
            $item->image = 'img/resource/' . $filename;
        } elseif ($request->filled('image')) {
            $item->image = $request->input('image');
        }
        $item->slot          = $request->filled('slot') ? ShareItemSlot::from($request->input('slot')) : null;
        $item->price         = (int) $request->input('price', 0);
        $item->break_crystal = (int) $request->input('break_crystal', 0);
        $item->count_use     = (int) $request->input('count_use', 0);
        $item->min_attack    = (int) $request->input('min_attack', 0);
        $item->max_attack    = (int) $request->input('max_attack', 0);
        $item->armor         = (int) $request->input('armor', 0);
        $item->is_two_hand   = (bool) $request->input('is_two_hand', false);
        $item->is_active     = (bool) $request->input('is_active', true);
        $item->is_heal       = (bool) $request->input('is_heal', false);
        $item->is_sell       = (bool) $request->input('is_sell', true);
        $item->is_weight     = (bool) $request->input('is_weight', true);
        $item->is_slot_usable= (bool) $request->input('is_slot_usable', false);
        $item->skill_id      = $request->filled('skill_id') ? (int) $request->input('skill_id') : null;
        $item->skill_lvl     = $request->filled('skill_lvl') ? (int) $request->input('skill_lvl') : null;
        $item->skill_exp     = $request->filled('skill_exp') ? (int) $request->input('skill_exp') : null;

        // Свиток заточки
        $item->upgrade_scroll_type = $request->filled('upgrade_scroll_type')
            ? UpgradeScrollType::from($request->input('upgrade_scroll_type'))
            : null;

        // Камень
        if ($type === ShareItemType::GEM) {
            $raw = $request->input('gem_stats_json', '[]');
            $item->gem_stats = json_decode($raw, true) ?: [];
        }

        // Руна
        if ($type === ShareItemType::RUNE) {
            $raw = $request->input('rune_rarity');
            $item->rune_rarity = $raw ? RuneRarity::from($raw) : null;
            $pool                 = $request->input('rune_stat_pool', []);
            $item->rune_stat_pool = count($pool) > 0 ? $pool : null;
        }
    }
}