<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Application\Services\ManagePaymentSettings;
use App\Modules\Payments\Application\Services\PaymentOrderService;
use App\Modules\Payments\Domain\Enums\PaymentOrderStatus;
use App\Modules\Payments\Infrastructure\Persistence\Models\PaymentOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PaymentOrderController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'status' => ['nullable', Rule::enum(PaymentOrderStatus::class)],
            'user_id' => ['nullable', 'integer', 'min:1'], 'order' => ['nullable', 'uuid'],
        ]);
        $orders = PaymentOrder::query()->with(['user', 'reviewer'])
            ->when($filter['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filter['user_id'] ?? null, fn ($query, $value) => $query->where('user_id', $value))
            ->when($filter['order'] ?? null, fn ($query, $value) => $query->where('public_id', $value))
            ->orderByDesc('id')->paginate(50)->withQueryString();

        return view('payments::admin.index', [
            'orders' => $orders, 'statuses' => PaymentOrderStatus::cases(),
            'settings' => DB::table('payment_settings')->find(1),
        ]);
    }

    public function show(string $publicId): View
    {
        $order = PaymentOrder::query()->with(['user', 'reviewer'])->where('public_id', $publicId)->firstOrFail();
        $events = DB::table('payment_order_events')->leftJoin('users', 'users.id', '=', 'payment_order_events.actor_id')
            ->where('payment_order_id', $order->id)->select('payment_order_events.*', 'users.name as actor_name')->orderBy('payment_order_events.id')->get();

        return view('payments::admin.show', compact('order', 'events'));
    }

    public function confirm(Request $request, string $publicId, PaymentOrderService $service): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:1000'], 'confirmed_payment' => ['accepted']]);
        $service->confirm($publicId, $request->user()->id, $data['note'] ?? null);

        return redirect()->route('admin.payment_orders.show', $publicId)->with('message', 'Оплата подтверждена. Бриллианты начислены.');
    }

    public function reject(Request $request, string $publicId, PaymentOrderService $service): RedirectResponse
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:1000']]);
        $service->reject($publicId, $request->user()->id, $data['note']);

        return redirect()->route('admin.payment_orders.show', $publicId)->with('message', 'Ордер отклонён.');
    }

    public function settings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'diamond_price' => ['required', 'regex:/^\d{1,5}(?:\.\d{1,2})?$/D'],
            'minimum_amount' => ['required', 'regex:/^\d{1,7}(?:\.\d{1,2})?$/D'],
            'is_enabled' => ['required', 'boolean'],
        ]);
        app(ManagePaymentSettings::class)->execute($data);

        return redirect()->route('admin.payment_orders.index')->with('message', 'Настройки покупки сохранены.');
    }
}
