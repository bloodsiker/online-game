<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Bank\Infrastructure\Persistence\Models\BankStock;
use App\Modules\Structure\Bank\Infrastructure\Persistence\Models\BankStockTier;
use App\Modules\Structure\Bank\Infrastructure\Persistence\Models\BankStockTierItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankStockController extends Controller
{
    public function list()
    {
        $stocks = BankStock::withCount('tiers')->orderByDesc('id')->get();

        return view('admin.bank.list', compact('stocks'));
    }

    public function create(): mixed
    {
        return view('admin.bank.info', ['stock' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $stock = BankStock::create([
            'name' => $request->input('name'),
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
            'is_active' => (bool) $request->input('is_active', false),
        ]);

        return redirect()->route('admin.bank.stock.info', $stock->id)->with('success', 'Акция создана.');
    }

    public function info(Request $request, BankStock $stock): mixed
    {
        if ($request->isMethod('POST') && $request->input('_action') === 'update') {
            $stock->update([
                'name' => $request->input('name'),
                'starts_at' => $request->input('starts_at'),
                'ends_at' => $request->input('ends_at'),
                'is_active' => (bool) $request->input('is_active', false),
            ]);

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $stock->load(['tiers.items.shareItem']);

        return view('admin.bank.info', compact('stock'));
    }

    public function addTier(Request $request, BankStock $stock): RedirectResponse
    {
        $threshold = (float) str_replace(',', '.', $request->input('diamond_threshold', '0'));
        $order = $stock->tiers()->max('sort_order') + 1;

        BankStockTier::create([
            'stock_id' => $stock->id,
            'diamond_threshold' => $threshold,
            'sort_order' => $order,
        ]);

        return redirect()->back()->with('success', 'Уровень добавлен.');
    }

    public function deleteTier(BankStock $stock, BankStockTier $tier): RedirectResponse
    {
        $tier->delete();

        return redirect()->back()->with('success', 'Уровень удалён.');
    }

    public function addTierItem(Request $request, BankStock $stock, BankStockTier $tier): RedirectResponse
    {
        $order = $tier->items()->max('sort_order') + 1;

        BankStockTierItem::create([
            'tier_id' => $tier->id,
            'share_item_id' => (int) $request->input('share_item_id'),
            'count' => (int) $request->input('count', 0),
            'sort_order' => $order,
        ]);

        return redirect()->back()->with('success', 'Предмет добавлен.');
    }

    public function deleteTierItem(BankStock $stock, BankStockTier $tier, BankStockTierItem $item): RedirectResponse
    {
        $item->delete();

        return redirect()->back()->with('success', 'Предмет удалён.');
    }

    public function delete(BankStock $stock): RedirectResponse
    {
        $stock->delete();

        return redirect()->route('admin.bank.stocks')->with('success', 'Акция удалена.');
    }

    public function duplicate(BankStock $stock): RedirectResponse
    {
        $stock->load('tiers.items.shareItem');

        $copy = BankStock::create([
            'name' => $stock->name.' (копия)',
            'starts_at' => $stock->starts_at,
            'ends_at' => $stock->ends_at,
            'is_active' => false,
        ]);

        foreach ($stock->tiers as $tier) {
            $newTier = BankStockTier::create([
                'stock_id' => $copy->id,
                'diamond_threshold' => $tier->diamond_threshold,
                'sort_order' => $tier->sort_order,
            ]);

            foreach ($tier->items as $item) {
                BankStockTierItem::create([
                    'tier_id' => $newTier->id,
                    'share_item_id' => $item->share_item_id,
                    'count' => $item->count,
                    'sort_order' => $item->sort_order,
                ]);
            }
        }

        return redirect()->route('admin.bank.stock.info', $copy->id)->with('success', 'Акция скопирована.');
    }
}
