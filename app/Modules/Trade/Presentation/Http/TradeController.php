<?php

declare(strict_types=1);

namespace App\Modules\Trade\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Trade\Application\Services\TradeService;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function show(Request $request, int $post, TradeService $service, ItemTooltipCollector $tooltips)
    {
        $page = $service->page($request->user(), $post);
        $page['activeTab'] = $request->query('tab') === 'sale' ? 'sale' : 'purchase';
        $page['itemTooltipScript'] = $tooltips->collectFrom(new ShareItemTooltipStrategy($page['items']))->renderScript();

        return view('trade::index', $page);
    }

    public function act(Request $request, int $post, string $action, TradeService $service)
    {
        $data = $request->validate([
            'request_key' => ['required', 'uuid'],
            'trade_good_id' => [$action === 'license' ? 'nullable' : 'required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'tab' => ['nullable', 'in:purchase,sale'],
        ]);
        $result = $service->act($request->user(), $post, $action,
            $action === 'license' ? null : (int) $data['trade_good_id'],
            $action === 'license' ? 1 : (int) $data['quantity'], $data['request_key']);

        return redirect()->route('trade.show', [
            'post' => $post,
            'tab' => $action === 'sale' ? 'sale' : ($action === 'license' ? ($data['tab'] ?? 'purchase') : 'purchase'),
        ])->with('trade_result', $result);
    }
}
