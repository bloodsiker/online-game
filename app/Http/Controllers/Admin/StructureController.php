<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareAction;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StructureController extends Controller
{
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

        $structure->load(['location', 'npc', 'categories', 'shopItems.item', 'shopItems.category', 'actions']);
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
        $categoryId = $request->integer('share_structure_category_id');

        if ($categoryId > 0) {
            $structure->categories()->syncWithoutDetaching([$categoryId]);
        }

        return redirect()->back()->with('success', 'Категория добавлена.');
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
        $shopItem->save();

        return redirect()->back()->with('success', 'Товар обновлён.');
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
}
