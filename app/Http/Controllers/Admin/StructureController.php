<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareAction;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItemRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'string', 'in:'.implode(',', array_keys(Structure::TYPES))],
                'location_id' => ['nullable', 'integer', 'exists:locations,id'],
                'npc_id' => ['nullable', 'integer', 'exists:npcs,id'],
            ]);

            $structure = new Structure;
            $structure->name = $data['name'];
            $structure->type = $data['type'];
            $structure->location_id = $data['location_id'] ?? null;
            $structure->npc_id = $data['npc_id'] ?? null;
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

    public function info(Request $request, Structure $structure): mixed
    {
        if ($request->isMethod('POST')) {
            $structure->name = $request->input('name');
            $structure->type = $request->input('type');
            $structure->location_id = $request->input('location_id') ?: null;
            $structure->npc_id = $request->input('npc_id') ?: null;
            $structure->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $structure->load([
            'location',
            'npc',
            'categories',
            'shopItems.item',
            'shopItems.category',
            'shopItems.requirements.item',
            'actions',
        ]);
        $allActions = ShareAction::orderBy('name')->get();
        $allCategories = ShareStructureCategory::orderBy('name')->get();

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

    public function infoCategory(Request $request, Structure $structure): RedirectResponse
    {
        $categoryName = trim((string) $request->input('category_name'));
        $categoryId = $request->integer('share_structure_category_id');

        if ($categoryName !== '') {
            $categoryId = (int) ShareStructureCategory::firstOrCreate(
                ['name' => $categoryName],
                ['is_active' => true],
            )->id;
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
        $structure->actions()->syncWithoutDetaching([(int) $request->input('share_action_id')]);

        return redirect()->back()->with('success', 'Действие добавлено.');
    }

    public function infoActionDelete(Structure $structure, ShareAction $action): RedirectResponse
    {
        $structure->actions()->detach($action->id);

        return redirect()->back()->with('success', 'Действие удалено.');
    }

    private function ensureShopItemBelongsToStructure(Structure $structure, ShopItem $shopItem): void
    {
        abort_unless((int) $shopItem->structure_id === (int) $structure->id, 404);
    }
}
