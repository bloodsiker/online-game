<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ItemRarity;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemEffect;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Structure\Blacksmith\Domain\Enums\RuneRarity;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function list(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'type' => (string) $request->query('type', ''),
            'rarity' => (string) $request->query('rarity', ''),
            'slot' => (string) $request->query('slot', ''),
        ];

        $listItems = ShareItem::query()
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $search = '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['q']).'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when(ShareItemType::tryFrom($filters['type']) !== null, fn ($query) => $query->where('type', $filters['type']))
            ->when(ItemRarity::tryFrom($filters['rarity']) !== null, fn ($query) => $query->where('rarity', $filters['rarity']))
            ->when(ShareItemSlot::tryFrom($filters['slot']) !== null, fn ($query) => $query->where('slot', $filters['slot']))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $types = ShareItemType::cases();
        $rarities = ItemRarity::cases();
        $slots = ShareItemSlot::cases();

        return view('admin.item.list', compact('listItems', 'filters', 'types', 'rarities', 'slots'));
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
                'percent' => 100,
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

        $item->load(['recipe', 'recipe.items', 'recipe.kraftItem', 'stats', 'effects', 'requirements.skill']);

        $skills = Skill::orderBy('name')->get();
        $statTypes = ShareItemStatType::cases();
        $effectTypes = ItemEffectType::cases();
        $requirementTypes = ShareItemRequirementType::cases();
        $playerStatKeys = PlayerStatKey::cases();
        $rarities = ItemRarity::cases();

        return view('admin.item.info', compact('item', 'skills', 'statTypes', 'effectTypes', 'requirementTypes', 'playerStatKeys', 'rarities'));
    }

    public function addStat(Request $request, ShareItem $item): RedirectResponse
    {
        ShareItemStat::create([
            'share_item_id' => $item->id,
            'stat_type' => ShareItemStatType::from($request->input('stat_type')),
            'value' => (int) $request->input('value', 0),
            'value_type' => ItemEffectValueType::from($request->input('value_type', 'flat')),
        ]);

        return redirect()->back()->with('success', 'Стат добавлен.');
    }

    public function deleteStat(ShareItem $item, ShareItemStat $stat): RedirectResponse
    {
        $stat->delete();

        return redirect()->back()->with('success', 'Стат удалён.');
    }

    public function addEffect(Request $request, ShareItem $item): RedirectResponse
    {
        ShareItemEffect::create([
            'share_item_id' => $item->id,
            'effect_type' => ItemEffectType::from($request->input('effect_type')),
            'value' => (int) $request->input('value', 0),
            'value_type' => ItemEffectValueType::from($request->input('value_type', 'flat')),
            'duration_seconds' => $request->filled('duration_seconds') ? (int) $request->input('duration_seconds') : null,
        ]);

        return redirect()->back()->with('success', 'Эффект добавлен.');
    }

    public function deleteEffect(ShareItem $item, ShareItemEffect $effect): RedirectResponse
    {
        $effect->delete();

        return redirect()->back()->with('success', 'Эффект удалён.');
    }

    public function updateRecipe(Request $request, ShareRecipe $recipe): RedirectResponse
    {
        $recipe->kraft_item_id = $request->filled('kraft_item_id') ? (int) $request->input('kraft_item_id') : null;
        $recipe->percent = (int) $request->input('percent', 100);
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

        $item->name = $request->input('name');
        $item->type = $type;
        $item->description = $request->input('description');
        $image = $request->file('image');
        if ($image instanceof UploadedFile) {
            $item->image = $this->storeItemImage($image);
        } elseif ($request->filled('image')) {
            $item->image = $request->input('image');
        }
        $item->rarity = ItemRarity::from($request->input('rarity', ItemRarity::COMMON->value));
        $item->slot = $request->filled('slot') ? ShareItemSlot::from($request->input('slot')) : null;
        $item->price = (int) $request->input('price', 0);
        $item->break_crystal = (int) $request->input('break_crystal', 0);
        $item->count_use = (int) $request->input('count_use', 0);
        $item->is_two_hand = (bool) $request->input('is_two_hand', false);
        $item->is_active = (bool) $request->input('is_active', true);
        $item->is_heal = (bool) $request->input('is_heal', false);
        $item->is_sell = (bool) $request->input('is_sell', true);
        $item->is_give = (bool) $request->input('is_give', true);
        $item->is_weight = (bool) $request->input('is_weight', true);
        $item->is_slot_usable = (bool) $request->input('is_slot_usable', false);
        $item->skill_id = $request->filled('skill_id') ? (int) $request->input('skill_id') : null;
        $item->skill_lvl = $request->filled('skill_lvl') ? (int) $request->input('skill_lvl') : null;
        $item->skill_exp = $request->filled('skill_exp') ? (int) $request->input('skill_exp') : null;

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
            $pool = $request->input('rune_stat_pool', []);
            $item->rune_stat_pool = count($pool) > 0 ? $pool : null;
        }
    }

    private function storeItemImage(UploadedFile $file): string
    {
        return '/storage/'.$file->store('items', 'public');
    }

    public function addRequirement(Request $request, ShareItem $item): RedirectResponse
    {
        $type = ShareItemRequirementType::from($request->input('type'));
        $statKey = $type === ShareItemRequirementType::STAT ? $request->input('stat_key') : null;
        $skillId = $type === ShareItemRequirementType::SKILL ? (int) $request->input('skill_id') : null;

        ShareItemRequirement::create([
            'share_item_id' => $item->id,
            'type' => $type,
            'stat_key' => $statKey,
            'skill_id' => $skillId,
            'min_value' => (int) $request->input('min_value', 1),
        ]);

        return redirect()->back()->with('success', 'Требование добавлено.');
    }

    public function deleteRequirement(ShareItem $item, ShareItemRequirement $requirement): RedirectResponse
    {
        $requirement->delete();

        return redirect()->back()->with('success', 'Требование удалено.');
    }
}
