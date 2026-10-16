<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareAction;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItemRequirement;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    private readonly AdminImageStorage $imageStorage;

    public function __construct(?AdminImageStorage $imageStorage = null)
    {
        $this->imageStorage = $imageStorage ?? new AdminImageStorage;
    }

    public function create(Request $request, ContentTranslationEditor $translationEditor): mixed
    {
        if ($request->isMethod('POST')) {
            $data = $request->validate([
                'type' => ['required', 'string', 'in:'.implode(',', array_keys(Structure::TYPES))],
                'location_id' => ['nullable', 'integer', 'exists:locations,id'],
                'npc_id' => ['nullable', 'integer', 'exists:npcs,id'],
                'dungeon_id' => ['nullable', 'integer', 'exists:dungeons,id'],
            ]);

            $structure = new Structure;
            $translationEditor->fill($structure, $request, ['name', 'description']);
            $structure->type = $data['type'];
            $structure->location_id = $data['location_id'] ?? null;
            $structure->npc_id = $data['npc_id'] ?? null;
            $structure->dungeon_id = $data['dungeon_id'] ?? null;
            $this->applyImage($structure, $request);
            $structure->save();

            return redirect()
                ->route('admin.structure.info', $structure->id)
                ->with('success', 'Построение создано.');
        }

        return view('admin.structures.create');
    }

    public function list()
    {
        $listStructures = Structure::with(['location', 'npc'])
            ->withCount(['shopItems', 'actions'])
            ->orderByDesc('id')
            ->get();

        return view('admin.structures.list', compact('listStructures'));
    }

    public function info(Request $request, Structure $structure, ContentTranslationEditor $translationEditor): mixed
    {
        if ($request->isMethod('POST')) {
            $data = $request->validate([
                'type' => ['required', 'string', 'in:'.implode(',', array_keys(Structure::TYPES))],
                'location_id' => ['nullable', 'integer', 'exists:locations,id'],
                'npc_id' => ['nullable', 'integer', 'exists:npcs,id'],
                'dungeon_id' => ['nullable', 'integer', 'exists:dungeons,id'],
            ]);
            $translationEditor->fill($structure, $request, ['name', 'description']);
            $structure->type = $data['type'];
            $structure->location_id = $data['location_id'] ?? null;
            $structure->npc_id = $data['npc_id'] ?? null;
            $structure->dungeon_id = $data['dungeon_id'] ?? null;
            $this->applyImage($structure, $request);
            $structure->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $structure->load([
            'location',
            'npc',
            'clanBossDungeon',
            'categories',
            'shopItems.item',
            'shopItems.category',
            'shopItems.requirements.item',
            'actions',
        ]);
        $allActions = ShareAction::orderBy('name->ru')->get();
        $allCategories = ShareStructureCategory::orderBy('name->ru')->get();

        return view('admin.structures.info', compact('structure', 'allActions', 'allCategories'));
    }

    public function infoShop(Request $request, Structure $structure): RedirectResponse
    {
        $categoryId = $request->integer('share_structure_category_id') ?: null;

        ShopItem::create([
            'structure_id' => $structure->id,
            'share_item_id' => (int) $request->input('share_item_id'),
            'share_structure_category_id' => $categoryId,
            'price' => (int) $request->input('price', 0),
            'diamond' => (int) $request->input('diamond', 0),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        if ($categoryId !== null) {
            $structure->categories()->syncWithoutDetaching([$categoryId]);
        }

        return redirect()->back()->with('success', 'Предмет добавлен.');
    }

    public function infoCategory(Request $request, Structure $structure, ContentTranslationEditor $translationEditor): RedirectResponse
    {
        $categoryId = $request->integer('share_structure_category_id');

        if ($request->filled('name') || $request->filled('translations.uk.name')) {
            $categoryName = trim((string) $request->input('name'));
            $category = $categoryName !== ''
                ? ShareStructureCategory::query()->where('name->ru', $categoryName)->first()
                : null;
            if ($category === null) {
                $category = new ShareStructureCategory;
                $translationEditor->fill($category, $request, ['name']);
                $category->is_active = true;
                $category->save();
            }
            $categoryId = (int) $category->id;
        }

        if ($categoryId > 0) {
            $structure->categories()->syncWithoutDetaching([$categoryId]);
        }

        return redirect()->back()->with('success', 'Категория добавлена.');
    }

    public function infoCategoryDelete(Structure $structure, ShareStructureCategory $category): RedirectResponse
    {
        $structure->shopItems()
            ->where('share_structure_category_id', $category->id)
            ->update(['share_structure_category_id' => null]);
        $structure->categories()->detach($category->id);

        return redirect()->back()->with('success', 'Категория убрана из магазина.');
    }

    public function infoShopDeleteItem(Structure $structure, ShareItem $item): RedirectResponse
    {
        ShopItem::where('structure_id', $structure->id)
            ->where('share_item_id', $item->id)
            ->delete();

        return redirect()->back()->with('success', 'Предмет удалён.');
    }

    public function infoShopUpdate(Request $request, Structure $structure, ShopItem $shopItem): RedirectResponse
    {
        if ((int) $shopItem->structure_id !== (int) $structure->id) {
            abort(404);
        }

        $shopItem->price = max(0, (int) $request->input('price', 0));
        $shopItem->diamond = max(0, (int) $request->input('diamond', 0));
        $shopItem->sort_order = (int) $request->input('sort_order', 0);
        $categoryId = $request->integer('share_structure_category_id') ?: null;
        if ($categoryId !== null) {
            abort_unless(
                $structure->categories()->whereKey($categoryId)->exists(),
                422,
                'Категория не подключена к этому магазину.',
            );
        }
        $shopItem->share_structure_category_id = $categoryId;
        $shopItem->save();

        return redirect()->back()->with('success', 'Товар обновлён.');
    }

    public function infoShopAddRequirement(Request $request, Structure $structure, ShopItem $shopItem): RedirectResponse
    {
        $this->ensureShopItemBelongsToStructure($structure, $shopItem);

        $data = $request->validate([
            'share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $requirement = $shopItem->requirements()->firstOrNew([
            'share_item_id' => (int) $data['share_item_id'],
        ]);
        $requirement->quantity = (int) $data['quantity'];
        $requirement->save();

        return redirect()->back()->with('success', 'Предмет добавлен в стоимость товара.');
    }

    public function infoShopDeleteRequirement(
        Structure $structure,
        ShopItem $shopItem,
        ShopItemRequirement $requirement,
    ): RedirectResponse {
        $this->ensureShopItemBelongsToStructure($structure, $shopItem);
        abort_unless((int) $requirement->shop_item_id === (int) $shopItem->id, 404);

        $requirement->delete();

        return redirect()->back()->with('success', 'Предмет убран из стоимости товара.');
    }

    public function infoAction(Request $request, Structure $structure): RedirectResponse
    {
        $data = $request->validate([
            'share_action_id' => ['required', 'integer', 'exists:share_actions,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $structure->actions()->syncWithoutDetaching([
            (int) $data['share_action_id'] => ['is_active' => $request->boolean('is_active', true)],
        ]);

        return redirect()->back()->with('success', 'Действие добавлено.');
    }

    public function updateAction(Request $request, Structure $structure, ShareAction $action): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        abort_unless($structure->actions()->whereKey($action->id)->exists(), 404);
        $structure->actions()->updateExistingPivot($action->id, ['is_active' => (bool) $data['is_active']]);

        return redirect()->back()->with('success', 'Состояние действия сохранено.');
    }

    public function infoActionDelete(Structure $structure, ShareAction $action): RedirectResponse
    {
        $structure->actions()->detach($action->id);

        return redirect()->back()->with('success', 'Действие удалено.');
    }

    private function applyImage(Structure $structure, Request $request): void
    {
        if ($request->hasFile('image')) {
            $oldImage = $structure->getRawOriginal('image');
            $structure->image = $this->imageStorage->storeOnPublicDisk($request->file('image'), 'structure');
            $this->deleteStorageImage($oldImage);
        } elseif ($request->boolean('delete_image')) {
            $this->deleteStorageImage($structure->getRawOriginal('image'));
            $structure->image = null;
        }
    }

    private function ensureShopItemBelongsToStructure(Structure $structure, ShopItem $shopItem): void
    {
        abort_unless((int) $shopItem->structure_id === (int) $structure->id, 404);
    }
}
