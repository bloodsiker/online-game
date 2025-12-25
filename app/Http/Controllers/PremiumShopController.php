<?php

namespace App\Http\Controllers;

use App\Models\ShareItem;
use App\Models\Shop\ShopItem;
use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PremiumShopController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $premiumShopId = 7;

        $shop = Structure::with('shopItems.item', 'shopItems.requirements.item')->find($premiumShopId);
        $firstCategory = $shop->categories()->first();
        $activeCategoryId = $request->integer('category_id', $firstCategory->id);

        $query = ShopItem::select('shop_items.*')
            ->with(['item', 'requirements.item'])
            ->where('shop_items.structure_id', $shop->id)
            ->where('share_structure_category_id', $activeCategoryId);

        $items = $query->orderBy('shop_items.id', 'desc')->get();

        return view('premium_shop.buy', compact('user', 'shop', 'activeCategoryId', 'items'));
    }
}
