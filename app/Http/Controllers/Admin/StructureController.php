<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareAction;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
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

        $structure->load(['location', 'npc', 'shopItems.item', 'actions']);
        $allActions = ShareAction::orderBy('name')->get();

        return view('admin.structures.info', compact('structure', 'allActions'));
    }

    public function infoShop(Request $request, Structure $structure): RedirectResponse
    {
        ShopItem::create([
            'structure_id' => $structure->id,
            'share_item_id' => (int) $request->input('share_item_id'),
            'price' => (int) $request->input('price', 0),
            'diamond' => (int) $request->input('diamond', 0),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->back()->with('success', 'Предмет добавлен.');
    }

    public function infoShopDeleteItem(Structure $structure, ShareItem $item): RedirectResponse
    {
        ShopItem::where('structure_id', $structure->id)
            ->where('share_item_id', $item->id)
            ->delete();

        return redirect()->back()->with('success', 'Предмет удалён.');
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
