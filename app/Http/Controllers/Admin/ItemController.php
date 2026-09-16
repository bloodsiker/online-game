<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Item\Infrastructure\Persistence\Models\ShareItemLockConfig;
use App\Modules\Item\Infrastructure\Persistence\Models\ShareItemLockpickConfig;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Modules\Share\Application\UseCases\Admin\DeleteShareItem;
use App\Modules\Share\Domain\Enums\GatheringToolFamily;
use App\Modules\Share\Domain\Enums\ItemBuffReapplyPolicy;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\RecipeUnlockType;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemBuff;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemDebuff;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemEffect;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemUseLimit;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Structure\Blacksmith\Domain\Enums\RunePassiveType;
use App\Modules\Structure\Blacksmith\Domain\Enums\RuneRarity;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ItemController extends Controller
{
    private readonly AdminImageStorage $imageStorage;

    public function __construct(?AdminImageStorage $imageStorage = null)
    {
        $this->imageStorage = $imageStorage ?? new AdminImageStorage;
    }

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
        $toolFamilies = GatheringToolFamily::cases();
        $magicSkills = MagicSkill::orderBy('name')->pluck('name', 'id');
        $claimedMagicSkillIds = MagicSkillBook::pluck('magic_skill_id')->all();
        $debuffEffects = Effect::query()->where('type', 'debuff')->orderBy('name')->get();

        return view('admin.item.create', compact('skills', 'toolFamilies', 'magicSkills', 'claimedMagicSkillIds', 'debuffEffects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $item = new ShareItem;
        $this->fillItem($item, $request);
        $item->save();
        $this->syncLockConfig($item, $request);
        $this->syncLockpickConfig($item, $request);

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
            $this->syncLockConfig($item, $request);
            $this->syncLockpickConfig($item, $request);

            $bookError = $this->syncMagicSkillBook($item, $request);
            if ($bookError !== null) {
                return redirect()->back()->with('error', $bookError);
            }

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $item->load([
            'recipe',
            'recipe.items',
            'recipe.kraftItem',
            'stats',
            'effects',
            'buffs.effect',
            'useLimit',
            'lockConfig',
            'lockpickConfig',
            'debuffs.effect',
            'requirements.skill',
            'magicSkillBook',
            'rarityUpgradeTarget',
            'rarityUpgradeMaterials',
            'itemHasItems' => fn ($query) => $query->orderBy('share_items.name'),
            'lockConfig.trapEffect',
        ]);

        $skills = Skill::orderBy('name')->get();
        $toolFamilies = GatheringToolFamily::cases();
        $magicSkills = MagicSkill::orderBy('name')->pluck('name', 'id');
        $claimedMagicSkillIds = MagicSkillBook::where('share_item_id', '!=', $item->id)->pluck('magic_skill_id')->all();
        $statTypes = ShareItemStatType::cases();
        $effectTypes = ItemEffectType::cases();
        $requirementTypes = ShareItemRequirementType::cases();
        $playerStatKeys = PlayerStatKey::cases();
        $rarities = ItemRarity::cases();
        $buffEffects = Effect::query()->where('type', 'buff')->orderBy('name')->get();
        $debuffEffects = Effect::query()->where('type', 'debuff')->orderBy('name')->get();
        $upgradeTargets = ShareItem::query()
            ->whereKeyNot($item->id)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'rarity']);

        return view('admin.item.info', compact('item', 'skills', 'toolFamilies', 'magicSkills', 'claimedMagicSkillIds', 'statTypes', 'effectTypes', 'requirementTypes', 'playerStatKeys', 'rarities', 'buffEffects', 'debuffEffects', 'upgradeTargets'));
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
            'buffs',
            'useLimit',
            'debuffs',
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

            foreach ($item->buffs as $buff) {
                $copy->buffs()->save($buff->replicate());
            }

            if ($item->useLimit !== null) {
                $copy->useLimit()->save($item->useLimit->replicate());
            }

            if ($item->lockConfig !== null) {
                $copy->lockConfig()->save($item->lockConfig->replicate());
            }

            foreach ($item->debuffs as $debuff) {
                $copy->debuffs()->save($debuff->replicate());
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

        $message = 'Предмет скопирован вместе с его характеристиками, эффектами, бафами, дебаффами, требованиями и составом.';
        if ($item->magicSkillBook !== null) {
            $message .= ' Привязка к заклинанию не скопирована: одно заклинание может быть связано только с одной книгой.';
        }

        return redirect()->route('admin.item.info', $copy->id)->with('success', $message);
    }

    public function destroy(ShareItem $item, DeleteShareItem $deleteItem): RedirectResponse
    {
        $name = $item->name;

        if (! $deleteItem->execute($item)) {
            return redirect()->back()->with('error', 'Нельзя удалить предмет «'.$name.'»: он используется в игровых настройках. Сначала удалите связанные записи или отключите предмет.');
        }

        return redirect()->route('admin.items')->with('success', 'Предмет «'.$name.'» удалён.');
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

    public function addBuff(Request $request, ShareItem $item): RedirectResponse
    {
        $data = $request->validate([
            'effect_id' => ['required', 'integer', 'exists:effects,id'],
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:604800'],
            'reapply_policy' => ['required', 'string', 'in:'.implode(',', array_column(ItemBuffReapplyPolicy::cases(), 'value'))],
        ]);

        $effect = Effect::query()->whereKey($data['effect_id'])->where('type', 'buff')->first();
        if ($effect === null) {
            return redirect()->back()->with('error', 'Для предмета можно выбрать только бафф.');
        }

        ShareItemBuff::query()->updateOrCreate(
            ['share_item_id' => $item->id, 'effect_id' => $effect->id],
            [
                'duration_seconds' => $data['duration_seconds'],
                'reapply_policy' => $data['reapply_policy'],
            ],
        );

        return redirect()->back()->with('success', 'Бафф добавлен.');
    }

    public function deleteBuff(ShareItem $item, ShareItemBuff $buff): RedirectResponse
    {
        abort_unless($buff->share_item_id === $item->id, 404);
        $buff->delete();

        return redirect()->back()->with('success', 'Бафф удалён.');
    }

    public function updateUseLimit(Request $request, ShareItem $item): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'max_uses' => ['required_if:enabled,1', 'nullable', 'integer', 'min:1', 'max:65535'],
            'period_value' => ['required_if:enabled,1', 'nullable', 'integer', 'min:1', 'max:525600'],
            'period_unit' => ['required_if:enabled,1', 'nullable', 'string', 'in:minutes,hours,days'],
        ]);

        if (! $request->boolean('enabled')) {
            $item->useLimit()->delete();

            return redirect()->back()->with('success', 'Ограничение использования отключено.');
        }

        $multiplier = match ($data['period_unit']) {
            'minutes' => 60,
            'hours' => 3600,
            'days' => 86400,
        };
        $periodSeconds = (int) $data['period_value'] * $multiplier;
        if ($periodSeconds > 31536000) {
            return redirect()->back()->withErrors(['period_value' => 'Период не может превышать 365 дней.'])->withInput();
        }

        DB::transaction(function () use ($item, $data, $periodSeconds): void {
            $limit = ShareItemUseLimit::query()->updateOrCreate(
                ['share_item_id' => $item->id],
                [
                    'max_uses' => (int) $data['max_uses'],
                    'period_seconds' => $periodSeconds,
                ],
            );

            // После изменения правила старые окна больше не соответствуют настройке.
            $limit->playerStates()->delete();
        });

        return redirect()->back()->with('success', 'Ограничение использования сохранено. Текущие периоды игроков сброшены.');
    }

    public function addDebuff(Request $request, ShareItem $item): RedirectResponse
    {
        $data = $request->validate([
            'effect_id' => ['required', 'integer', 'exists:effects,id'],
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:604800'],
        ]);
        $effect = Effect::query()->whereKey($data['effect_id'])->where('type', 'debuff')->first();
        if ($effect === null) {
            return redirect()->back()->with('error', 'Для предмета можно выбрать только дебафф.');
        }
        ShareItemDebuff::query()->updateOrCreate(
            ['share_item_id' => $item->id, 'effect_id' => $effect->id],
            ['duration_seconds' => $data['duration_seconds']],
        );

        return redirect()->back()->with('success', 'Дебафф добавлен.');
    }

    public function deleteDebuff(ShareItem $item, ShareItemDebuff $debuff): RedirectResponse
    {
        abort_unless($debuff->share_item_id === $item->id, 404);
        $debuff->delete();

        return redirect()->back()->with('success', 'Дебафф удалён.');
    }

    public function updateRecipe(Request $request, ShareRecipe $recipe): RedirectResponse
    {
        $data = $request->validate([
            'kraft_item_id' => ['nullable', 'integer', 'exists:share_items,id'],
            'percent' => ['required', 'integer', 'min:0', 'max:100'],
            'unlock_type' => ['required', 'string', 'in:'.implode(',', array_column(RecipeUnlockType::cases(), 'value'))],
        ]);

        $recipe->kraft_item_id = $data['kraft_item_id'] ?? null;
        $recipe->percent = $data['percent'];
        $recipe->unlock_type = RecipeUnlockType::from($data['unlock_type']);
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

    public function addRarityUpgradeMaterial(Request $request, ShareItem $item): RedirectResponse
    {
        $data = $request->validate([
            'share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'count' => ['required', 'integer', 'min:1', 'max:999999'],
        ]);

        $item->rarityUpgradeMaterials()->syncWithoutDetaching([
            (int) $data['share_item_id'] => ['count' => (int) $data['count']],
        ]);

        return redirect()->back()->with('success', 'Материал для апгрейда добавлен.');
    }

    public function deleteRarityUpgradeMaterial(ShareItem $item, ShareItem $material): RedirectResponse
    {
        $item->rarityUpgradeMaterials()->detach($material->id);

        return redirect()->back()->with('success', 'Материал для апгрейда удалён.');
    }

    public function addChestContent(Request $request, ShareItem $item): RedirectResponse
    {
        $this->ensureContentsSupported($item);
        $data = $this->validateChestContent($request, includeItem: true);
        $containedItemId = (int) $data['share_item_id'];

        if ($containedItemId === (int) $item->id) {
            return redirect()->back()->with('error', 'Предмет нельзя добавить в собственное содержимое.');
        }

        $item->itemHasItems()->syncWithoutDetaching([
            $containedItemId => $this->chestContentPivot($data),
        ]);

        return redirect()->back()->with('success', 'Предмет добавлен.');
    }

    public function updateChestContent(Request $request, ShareItem $item, ShareItem $containedItem): RedirectResponse
    {
        $this->ensureContentsSupported($item);
        abort_unless($item->itemHasItems()->whereKey($containedItem->id)->exists(), 404);

        $data = $this->validateChestContent($request);
        $item->itemHasItems()->updateExistingPivot($containedItem->id, $this->chestContentPivot($data));

        return redirect()->back()->with('success', 'Параметры предмета обновлены.');
    }

    public function deleteChestContent(ShareItem $item, ShareItem $containedItem): RedirectResponse
    {
        $this->ensureContentsSupported($item);
        $item->itemHasItems()->detach($containedItem->id);

        return redirect()->back()->with('success', 'Предмет удалён.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{share_item_id?: int|string, drop_chance: int|string, min_count: int|string, max_count: int|string} */
    private function validateChestContent(Request $request, bool $includeItem = false): array
    {
        $rules = [
            'drop_chance' => ['required', 'integer', 'between:0,100'],
            'min_count' => ['required', 'integer', 'min:1', 'max:999999'],
            'max_count' => ['required', 'integer', 'min:1', 'max:999999', 'gte:min_count'],
        ];

        if ($includeItem) {
            $rules['share_item_id'] = ['required', 'integer', 'exists:share_items,id'];
        }

        return $request->validate($rules);
    }

    /**
     * @param  array{drop_chance: int|string, min_count: int|string, max_count: int|string}  $data
     * @return array{drop_chance: int, min_count: int, max_count: int}
     */
    private function chestContentPivot(array $data): array
    {
        return [
            'drop_chance' => (int) $data['drop_chance'],
            'min_count' => (int) $data['min_count'],
            'max_count' => (int) $data['max_count'],
        ];
    }

    private function ensureContentsSupported(ShareItem $item): void
    {
        abort_unless(in_array($item->type, [ShareItemType::CHEST, ShareItemType::RESOURCE], true), 404);
    }

    private function fillItem(ShareItem $item, Request $request): void
    {
        $request->validate([
            'expire' => ['nullable', 'integer', 'min:1'],
            'max_drop_level_difference' => ['nullable', 'integer', 'between:0,255'],
            'gathering_time_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'gathering_respawn_seconds' => ['nullable', 'integer', 'min:1', 'max:604800'],
            'gathering_tool_family' => ['nullable', 'string', 'in:'.implode(',', array_column(GatheringToolFamily::cases(), 'value'))],
            'tool_family' => ['nullable', 'string', 'in:'.implode(',', array_column(GatheringToolFamily::cases(), 'value'))],
            'gathering_speed_bonus_percent' => ['nullable', 'integer', 'between:0,100'],
            'gathering_double_chance_percent' => ['nullable', 'integer', 'between:0,100'],
            'upgrade_to_share_item_id' => ['nullable', 'integer', 'exists:share_items,id'],
            'upgrade_gold_cost' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'lock_required_skill' => ['nullable', 'integer', 'between:0,300'],
            'lock_duration_seconds' => ['nullable', 'integer', 'between:2,3600'],
            'lockpicking_experience_reward' => ['nullable', 'integer', 'between:1,65535'],
            'trap_chance_penalty_percent' => ['nullable', 'integer', 'between:0,95'],
            'trap_effect_id' => ['nullable', 'integer', 'exists:effects,id'],
            'trap_effect_duration_seconds' => ['nullable', 'integer', 'between:1,86400'],
            'trap_damage_percent' => ['nullable', 'integer', 'between:0,100'],
            'lockpick_tier' => ['nullable', 'integer', 'between:1,6'],
            'lockpick_speed_bonus_percent' => ['nullable', 'integer', 'between:0,90'],
            'lockpick_failure_preserve_chance_percent' => ['nullable', 'integer', 'between:0,100'],
            'lockpick_trap_avoid_chance_percent' => ['nullable', 'integer', 'between:0,100'],
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
        $transparentImage = $request->file('transparent_image');
        if ($transparentImage instanceof UploadedFile) {
            $oldTransparentImage = $item->getRawOriginal('transparent_image');
            $item->transparent_image = $this->storeItemImage($transparentImage);
            $this->deleteStorageImage($oldTransparentImage);
        } elseif ($request->boolean('delete_transparent_image')) {
            $this->deleteStorageImage($item->getRawOriginal('transparent_image'));
            $item->transparent_image = null;
        }
        $item->rarity = ItemRarity::from($request->input('rarity', ItemRarity::COMMON->value));
        $item->slot = $type === ShareItemType::TOOL
            ? ShareItemSlot::HAND
            : ($request->filled('slot') ? ShareItemSlot::from($request->input('slot')) : null);
        $item->price = (int) $request->input('price', 0);
        $item->upgrade_to_share_item_id = $request->filled('upgrade_to_share_item_id')
            ? (int) $request->input('upgrade_to_share_item_id')
            : null;
        if ($item->upgrade_to_share_item_id === $item->id) {
            $item->upgrade_to_share_item_id = null;
        }
        $item->upgrade_gold_cost = max(0, (int) $request->input('upgrade_gold_cost', 0));
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
        $item->is_clan_warehouse_allowed = (bool) $request->input('is_clan_warehouse_allowed', true);
        $item->is_droppable = (bool) $request->input('is_droppable', true);
        $item->is_stackable = $type->isEquipment() || $type === ShareItemType::CHEST
            ? false
            : (bool) $request->input('is_stackable', false);
        $item->is_weight = (bool) $request->input('is_weight', true);
        $item->is_slot_usable = (bool) $request->input('is_slot_usable', false);
        $item->is_use = (bool) $request->input('is_use', false);
        $item->is_lockpick = (bool) $request->input('is_lockpick', false);
        $item->skill_id = $request->filled('skill_id') ? (int) $request->input('skill_id') : null;
        $item->skill_lvl = $request->filled('skill_lvl') ? (int) $request->input('skill_lvl') : null;
        $item->skill_exp = $request->filled('skill_exp') ? (int) $request->input('skill_exp') : null;
        $item->gathering_time_seconds = $type->isGatheringResource() && $request->filled('gathering_time_seconds')
            ? (int) $request->input('gathering_time_seconds')
            : null;
        $item->gathering_respawn_seconds = $type->isGatheringResource() && $request->filled('gathering_respawn_seconds')
            ? (int) $request->input('gathering_respawn_seconds')
            : null;
        $item->gathering_tool_family = $type->isGatheringResource() && $request->filled('gathering_tool_family')
            ? $request->input('gathering_tool_family')
            : null;
        $item->tool_family = $type === ShareItemType::TOOL && $request->filled('tool_family')
            ? $request->input('tool_family')
            : null;
        $item->gathering_speed_bonus_percent = $type === ShareItemType::TOOL
            ? min(100, max(0, (int) $request->input('gathering_speed_bonus_percent', 0)))
            : 0;
        $item->gathering_double_chance_percent = $type === ShareItemType::TOOL
            ? min(100, max(0, (int) $request->input('gathering_double_chance_percent', 0)))
            : 0;

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

        // Встроенная пассивка (оружие/щит)
        if (in_array($type, [ShareItemType::WEAPON, ShareItemType::SHIELD], true)) {
            $raw = $request->input('innate_passive_type');
            $item->innate_passive_type = $raw ? RunePassiveType::from($raw) : null;
            $item->innate_passive_value = $item->innate_passive_type !== null && $request->filled('innate_passive_value')
                ? (int) $request->input('innate_passive_value')
                : null;
        } else {
            $item->innate_passive_type = null;
            $item->innate_passive_value = null;
        }
    }

    private function syncLockConfig(ShareItem $item, Request $request): void
    {
        if ($item->type !== ShareItemType::CHEST) {
            $item->lockConfig()->delete();

            return;
        }

        ShareItemLockConfig::query()->updateOrCreate(
            ['share_item_id' => $item->id],
            [
                'lock_required_skill' => (int) $request->input('lock_required_skill', 0),
                'lock_duration_seconds' => (int) $request->input('lock_duration_seconds', 12),
                'experience_reward' => $request->filled('lockpicking_experience_reward')
                    ? (int) $request->input('lockpicking_experience_reward')
                    : null,
                'trap_chance_penalty_percent' => (int) $request->input('trap_chance_penalty_percent', 0),
                'trap_effect_id' => $request->filled('trap_effect_id') ? $request->integer('trap_effect_id') : null,
                'trap_effect_duration_seconds' => (int) $request->input('trap_effect_duration_seconds', 60),
                'trap_damage_percent' => (int) $request->input('trap_damage_percent', 0),
            ],
        );
    }

    private function syncLockpickConfig(ShareItem $item, Request $request): void
    {
        if (! $item->is_lockpick) {
            $item->lockpickConfig()->delete();

            return;
        }

        ShareItemLockpickConfig::query()->updateOrCreate(
            ['share_item_id' => $item->id],
            [
                'tier' => (int) $request->input('lockpick_tier', 1),
                'speed_bonus_percent' => (int) $request->input('lockpick_speed_bonus_percent', 0),
                'failure_preserve_chance_percent' => (int) $request->input('lockpick_failure_preserve_chance_percent', 0),
                'trap_avoid_chance_percent' => (int) $request->input('lockpick_trap_avoid_chance_percent', 0),
            ],
        );
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
        return $this->imageStorage->storeOnPublicDisk($file, 'items');
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

        return redirect()
            ->to(route('admin.item.info', $item->id).'#tab-requirements')
            ->with('success', 'Требование добавлено.');
    }

    public function deleteRequirement(ShareItem $item, ShareItemRequirement $requirement): RedirectResponse
    {
        $requirement->delete();

        return redirect()
            ->to(route('admin.item.info', $item->id).'#tab-requirements')
            ->with('success', 'Требование удалено.');
    }
}
