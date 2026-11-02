<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Application\Services\PaymentOrderService;
use App\Modules\Payments\Infrastructure\Persistence\Models\PaymentOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PaymentOrderController extends Controller
{
    public function store(Request $request, PaymentOrderService $service): RedirectResponse
    {
        $data = $request->validate([
            'diamonds' => ['required', 'integer', 'min:1', 'max:30000'],
            'request_id' => ['required', 'uuid'], 'agree' => ['accepted'],
        ]);
        $order = $service->create($request->user()->id, (int) $data['diamonds'], $data['request_id']);

        return redirect()->route('payments.orders.show', $order->public_id);
    }

    public function show(Request $request, string $publicId): View
    {
        return view('payments::order', ['order' => $this->ownedOrder($request, $publicId)]);
    }

    public function status(Request $request, string $publicId): JsonResponse
    {
        $order = $this->ownedOrder($request, $publicId);

        return response()->json([
            'status' => $order->status->value, 'status_label' => $order->status->label(),
            'credited_at' => $order->credited_at?->toIso8601String(), 'note' => $order->review_note,
            'money' => $request->user()->money, 'diamond' => $request->user()->diamond,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function cancel(Request $request, string $publicId, PaymentOrderService $service): RedirectResponse
    {
        $service->cancel($publicId, $request->user()->id);

        return redirect()->route('payments.orders.show', $publicId);
    }

    private function ownedOrder(Request $request, string $publicId): PaymentOrder
    {
        return PaymentOrder::query()->where('public_id', $publicId)->where('user_id', $request->user()->id)->firstOrFail();
    }
}
