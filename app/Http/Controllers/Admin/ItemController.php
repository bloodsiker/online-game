<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemEffect;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Structure\Blacksmith\Domain\Enums\RuneRarity;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
        $magicSkills = MagicSkill::orderBy('name')->pluck('name', 'id');
        $claimedMagicSkillIds = MagicSkillBook::pluck('magic_skill_id')->all();

        return view('admin.item.create', compact('skills', 'magicSkills', 'claimedMagicSkillIds'));
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

        // Зовём всегда, а не только для BOOK: syncMagicSkillBook() сам снимает
        // привязку, если предмет книгой не является (см. IMPORTANT 10).
        $bookError = $this->syncMagicSkillBook($item, $request);
        if ($bookError !== null) {
            return redirect()->route('admin.item.info', ['item' => $item->id])->with('error', $bookError);
        }

        return redirect()->route('admin.item.info', ['item' => $item->id])
            ->with('success', 'Предмет создан.');
    }

    public function info(Request $request, ShareItem $item): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillItem($item, $request);
            $item->save();

            $bookError = $this->syncMagicSkillBook($item, $request);
            if ($bookError !== null) {
                return redirect()->back()->with('error', $bookError);
            }

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $item->load(['recipe', 'recipe.items', 'recipe.kraftItem', 'stats', 'effects', 'requirements.skill', 'magicSkillBook']);

        $skills = Skill::orderBy('name')->get();
        $magicSkills = MagicSkill::orderBy('name')->pluck('name', 'id');
        $claimedMagicSkillIds = MagicSkillBook::where('share_item_id', '!=', $item->id)->pluck('magic_skill_id')->all();
        $statTypes = ShareItemStatType::cases();
        $effectTypes = ItemEffectType::cases();
        $requirementTypes = ShareItemRequirementType::cases();
        $playerStatKeys = PlayerStatKey::cases();
        $rarities = ItemRarity::cases();

        return view('admin.item.info', compact('item', 'skills', 'magicSkills', 'claimedMagicSkillIds', 'statTypes', 'effectTypes', 'requirementTypes', 'playerStatKeys', 'rarities'));
    }

    public function drop(ShareItem $item): View
    {
        $item->load([
            'monsters' => fn ($query) => $query
                ->orderBy('monsters.name')
                ->orderBy('monsters.lvl')
                ->orderBy('monsters.id'),
        ]);

        return view('admin.item.drop', compact('item'));
    }

    public function quests(ShareItem $item): View
    {
        $objectiveUsages = QuestObjective::query()
            ->with([
                'quest' => fn ($query) => $query->without('objectives'),
                'stage',
            ])
            ->where(function ($query) use ($item): void {
                $query->where('share_item_id', $item->id)
                    ->orWhere(function ($query) use ($item): void {
                        $query->where('target_type', 'item')
                            ->where('target_id', $item->id);
                    });
            })
            ->orderBy('quest_id')
            ->orderBy('stage_id')
            ->orderBy('id')
            ->get();

        $rewardUsages = QuestReward::query()
            ->with(['quest' => fn ($query) => $query->without('objectives')])
            ->where('share_item_id', $item->id)
            ->orderBy('quest_id')
            ->orderBy('id')
            ->get();

        return view('admin.item.quests', compact('item', 'objectiveUsages', 'rewardUsages'));
    }

    public function duplicate(ShareItem $item): RedirectResponse
    {
        $item->load([
            'stats',
            'effects',
            'requirements',
            'recipe.items',
            'itemHasItems',
            'magicSkillBook',
        ]);

        $copy = DB::transaction(function () use ($item): ShareItem {
            $copy = $item->replicate();
            $copy->name = $item->name.' (копия)';
            $copy->save();

            foreach ($item->stats as $stat) {
                $copy->stats()->save($stat->replicate());
            }

            foreach ($item->effects as $effect) {
                $copy->effects()->save($effect->replicate());
            }

            foreach ($item->requirements as $requirement) {
                $copy->requirements()->save($requirement->replicate());
            }

            if ($item->recipe !== null) {
                $recipe = $item->recipe->replicate();
                $recipe->share_item_id = $copy->id;
                $recipe->save();

                $recipe->items()->sync($item->recipe->items->mapWithKeys(
                    fn (ShareItem $ingredient): array => [$ingredient->id => ['count' => $ingredient->pivot->count]],
                )->all());
            }

            $copy->itemHasItems()->sync($item->itemHasItems->mapWithKeys(
                fn (ShareItem $containedItem): array => [$containedItem->id => [
                    'min_count' => $containedItem->pivot->min_count,
                    'max_count' => $containedItem->pivot->max_count,
                    'drop_chance' => $containedItem->pivot->drop_chance,
                ]],
            )->all());

            return $copy;
        });

        $message = 'Предмет скопирован вместе с его характеристиками, эффектами, требованиями и составом.';
        if ($item->magicSkillBook !== null) {
            $message .= ' Привязка к заклинанию не скопирована: одно заклинание может быть связано только с одной книгой.';
        }

        return redirect()->route('admin.item.info', $copy->id)->with('success', $message);
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
        $request->validate([
            'expire' => ['nullable', 'integer', 'min:1'],
            'max_drop_level_difference' => ['nullable', 'integer', 'between:0,255'],
        ]);

        $type = ShareItemType::from($request->input('type'));

        $item->name = $request->input('name');
        $item->type = $type;
        $item->description = $request->input('description');
        $image = $request->file('image');
        if ($image instanceof UploadedFile) {
            $oldImage = $item->getRawOriginal('image');
            $item->image = $this->storeItemImage($image);
            $this->deleteStorageImage($oldImage);
        } elseif ($request->filled('image')) {
            $item->image = $request->input('image');
        } elseif ($request->boolean('delete_image')) {
            $this->deleteStorageImage($item->getRawOriginal('image'));
            $item->image = null;
        }
        $item->rarity = ItemRarity::from($request->input('rarity', ItemRarity::COMMON->value));
        $item->slot = $request->filled('slot') ? ShareItemSlot::from($request->input('slot')) : null;
        $item->price = (int) $request->input('price', 0);
        $item->break_crystal = (int) $request->input('break_crystal', 0);
        $item->count_use = (int) $request->input('count_use', 0);
        $item->max_drop_level_difference = $request->filled('max_drop_level_difference')
            ? (int) $request->input('max_drop_level_difference')
            : null;
        $item->expire = $request->filled('expire') ? (int) $request->input('expire') : null;
        $item->is_two_hand = (bool) $request->input('is_two_hand', false);
        $item->is_active = (bool) $request->input('is_active', true);
        $item->is_sell = (bool) $request->input('is_sell', true);
        $item->is_auction_sellable = (bool) $request->input('is_auction_sellable', false);
        $item->is_give = (bool) $request->input('is_give', true);
        $item->is_droppable = (bool) $request->input('is_droppable', true);
        $item->is_stackable = (bool) $request->input('is_stackable', false);
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

    /** Возвращает текст ошибки, если заклинание уже привязано к другой книге, иначе null. */
    private function syncMagicSkillBook(ShareItem $item, Request $request): ?string
    {
        // Предмет не книга (в том числе — перестал ей быть после смены типа):
        // привязку надо снять. Иначе осиротевшая строка magic_skill_books
        // держит заклинание «занятым» (unique magic_skill_id не даёт привязать
        // его к настоящей книге), а LearnMagicSkillFromBook продолжает учить
        // заклинанию с предмета, который книгой уже не является.
        if ($item->type !== ShareItemType::BOOK) {
            MagicSkillBook::where('share_item_id', $item->id)->delete();

            return null;
        }

        $magicSkillId = $request->filled('magic_skill_id') ? (int) $request->input('magic_skill_id') : null;

        if ($magicSkillId === null) {
            MagicSkillBook::where('share_item_id', $item->id)->delete();

            return null;
        }

        $alreadyLinkedToAnotherBook = MagicSkillBook::where('magic_skill_id', $magicSkillId)
            ->where('share_item_id', '!=', $item->id)
            ->exists();

        if ($alreadyLinkedToAnotherBook) {
            return 'Это заклинание уже привязано к другой книге.';
        }

        MagicSkillBook::updateOrCreate(
            ['share_item_id' => $item->id],
            ['magic_skill_id' => $magicSkillId],
        );

        return null;
    }

    private function storeItemImage(UploadedFile $file): string
    {
        return $file->store('items', 'public');
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
