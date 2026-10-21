<?php

declare(strict_types=1);

namespace App\Modules\PlayerTrade\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use App\Modules\PlayerTrade\Application\Services\PlayerTradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PlayerTradeController extends Controller
{
    public function tooltip(Request $request, string $uuid, int $item, PlayerTradeService $service, ItemTooltipCollector $collector): JsonResponse
    {
        $instance = $service->itemForTooltip($request->user(), $uuid, $item);
        $collector->collectFrom(new ItemModelTooltipStrategy([$instance]));

        return response()->json(['tooltip' => $collector->all()[$instance->id]]);
    }

    public function invite(Request $request, int $user, PlayerTradeService $service): JsonResponse
    {
        try {
            $trade = $service->invite($request->user(), $user);

            return response()->json([
                'ok' => true,
                'message' => __('Предложение торговли отправлено.'),
                'url' => route('player-trade.show', $trade->uuid),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['ok' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Request $request, string $uuid, PlayerTradeService $service): View|JsonResponse
    {
        try {
            $page = $service->page($request->user(), $uuid);

            return view('player-trade::show', $page);
        } catch (RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            abort(403, $exception->getMessage());
        }
    }

    public function accept(Request $request, string $uuid, PlayerTradeService $service): JsonResponse
    {
        try {
            $trade = $service->accept($request->user(), $uuid);

            return response()->json([
                'message' => __('Торговля началась.'),
                'url' => route('player-trade.show', $trade->uuid),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function decline(Request $request, string $uuid, PlayerTradeService $service): JsonResponse
    {
        try {
            $service->decline($request->user(), $uuid);

            return response()->json(['message' => __('Вы отклонили предложение торговли.')]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function state(Request $request, string $uuid, PlayerTradeService $service): JsonResponse
    {
        try {
            return response()->json($service->state($request->user(), $uuid));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function saveOffer(Request $request, string $uuid, PlayerTradeService $service): JsonResponse
    {
        $data = $request->validate([
            'items' => ['array', 'max:100'],
            'items.*.backpack_id' => ['required', 'integer', 'min:1'],
            'items.*.count' => ['required', 'integer', 'min:1'],
            'money' => ['required', 'integer', 'min:0'],
        ]);

        try {
            return response()->json($service->saveOffer($request->user(), $uuid, $data['items'] ?? [], (int) $data['money']));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function confirm(Request $request, string $uuid, PlayerTradeService $service): JsonResponse
    {
        $data = $request->validate(['confirmed' => ['sometimes', 'boolean']]);

        try {
            return response()->json($service->confirm($request->user(), $uuid, (bool) ($data['confirmed'] ?? true)));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function cancel(Request $request, string $uuid, PlayerTradeService $service): JsonResponse
    {
        try {
            $service->cancel($request->user(), $uuid);

            return response()->json(['message' => __('Сделка отменена.')]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
