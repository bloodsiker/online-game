<?php

declare(strict_types=1);

namespace App\Modules\Trade\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use App\Modules\Trade\Application\Services\TradeAdminService;
use App\Modules\Trade\Infrastructure\Persistence\Models\TradePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    public function index()
    {
        return view('trade::admin', [
            'posts' => TradePost::query()->orderBy('id')->get(),
            'goods' => DB::table('trade_goods')->orderBy('id')->get(),
            'routes' => DB::table('trade_routes')->orderBy('id')->get(),
            'offers' => DB::table('trade_buy_offers')->orderBy('id')->get(),
            'stocks' => DB::table('trade_post_stocks')->orderBy('id')->get(),
            'logs' => DB::table('trade_logs')->orderByDesc('id')->paginate(50),
        ]);
    }

    public function save(Request $request, string $section, TradeAdminService $service, ContentTranslationEditor $translations)
    {
        $post = ['required', 'integer', 'exists:trade_posts,id'];
        $good = ['required', 'integer', 'exists:trade_goods,id'];
        $price = ['required', 'integer', 'min:0', 'max:1000000000'];
        $rules = match ($section) {
            'posts' => ['npc_id' => ['required', 'integer', 'exists:npcs,id'], 'license_price' => $price],
            'goods' => ['trade_post_id' => $post, 'share_item_id' => ['required', 'integer', 'exists:share_items,id'], 'base_price' => $price, 'required_skill_lvl' => ['required', 'integer', 'between:1,300'], 'sort_order' => ['required', 'integer', 'between:0,65535']],
            'routes' => ['trade_post_a_id' => $post, 'trade_post_b_id' => $post, 'distance_tier' => ['required', 'in:near,mid,far'], 'exp_per_unit' => ['required', 'integer', 'between:0,100000']],
            'offers' => ['trade_post_id' => $post, 'trade_good_id' => $good, 'buy_price' => $price],
            'stocks' => ['trade_good_id' => $good, 'current_quantity' => ['required', 'integer', 'between:0,1000000'], 'max_quantity' => ['required', 'integer', 'between:1,1000000'], 'restock_amount' => ['required', 'integer', 'between:0,1000000'], 'restock_interval_minutes' => ['required', 'integer', 'between:1,65535']],
        };
        $rules['id'] = ['nullable', 'integer', 'min:1'];
        if ($section !== 'stocks') {
            $rules['is_active'] = ['required', 'boolean'];
        }
        $data = $request->validate($rules);
        if ($section === 'posts') {
            $post = isset($data['id']) ? TradePost::query()->findOrFail($data['id']) : new TradePost;
            $translations->fill(model: $post, request: $request, fields: ['name']);
            $data['name'] = $post->getTranslations('name');
        }
        $service->save($section, $data);

        return redirect()->route('admin.trade.index')->with('success', 'Сохранено.');
    }
}
