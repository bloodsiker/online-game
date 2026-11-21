<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\Services;

use App\Modules\Payments\Domain\Enums\PaymentOrderStatus;
use App\Modules\Payments\Infrastructure\Heleket\HeleketClient;
use App\Modules\Payments\Infrastructure\Persistence\Models\PaymentOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

final class HeleketCheckout
{
    private const PAID = ['paid', 'paid_over'];

    private const CLOSED = ['cancel', 'fail', 'system_fail'];

    public function __construct(
        private readonly HeleketClient $client,
        private readonly PaymentOrderService $orders,
    ) {}

    /**
     * Ссылка на оплату. Heleket идемпотентен по order_id: повторный запрос вернёт тот же счёт,
     * поэтому при сбое сети ордер можно безопасно оплатить повторно.
     */
    public function paymentUrl(PaymentOrder $order): string
    {
        if ($order->provider !== 'heleket' || $order->status !== PaymentOrderStatus::PENDING) {
            throw ValidationException::withMessages(['order' => __('payments.already_processed')]);
        }
        if ($order->payment_url) {
            return $order->payment_url;
        }

        try {
            $invoice = $this->client->createInvoice([
                'amount' => number_format($order->amount_minor / 100, 2, '.', ''),
                'currency' => $order->currency,
                'order_id' => $order->public_id,
                'url_return' => route('payments.orders.show', $order->public_id),
                'url_success' => route('payments.orders.show', $order->public_id),
                'url_callback' => route('payments.heleket.webhook'),
                'is_payment_multiple' => false,
                'lifetime' => (int) config('services.heleket.lifetime', 3600),
                'additional_data' => 'user:'.$order->user_id,
            ]);
        } catch (Throwable $e) {
            Log::error('Heleket: не удалось создать счёт', ['order' => $order->public_id, 'error' => $e->getMessage()]);
            throw ValidationException::withMessages(['order' => __('payments.provider_error')]);
        }

        $order->update(['provider_payment_id' => $invoice['uuid'] ?? null, 'payment_url' => $invoice['url']]);

        return (string) $invoice['url'];
    }

    /** @param  array<string, mixed>  $data */
    public function handleWebhook(array $data): void
    {
        if (($data['type'] ?? null) !== 'payment' || ! is_string($data['order_id'] ?? null)) {
            return;
        }
        $order = PaymentOrder::query()->where('public_id', $data['order_id'])->where('provider', 'heleket')->first();
        if (! $order) {
            Log::warning('Heleket: вебхук для неизвестного ордера', ['order_id' => $data['order_id']]);

            return;
        }
        if ($order->provider_payment_id && isset($data['uuid']) && $data['uuid'] !== $order->provider_payment_id) {
            Log::warning('Heleket: uuid счёта не совпадает', ['order' => $order->public_id, 'uuid' => $data['uuid']]);

            return;
        }

        $status = (string) ($data['status'] ?? '');
        $tx = isset($data['txid']) ? ' tx '.$data['txid'] : '';
        $paidInfo = 'Heleket: '.$status.', '.($data['payment_amount'] ?? '?').' '.($data['payer_currency'] ?? '').$tx;

        if (in_array($status, self::PAID, true)) {
            // Сумма счёта фиксирована при создании — сверяем, что вебхук относится именно к ней.
            $invoiceMinor = (int) round(((float) ($data['amount'] ?? 0)) * 100);
            if (($data['currency'] ?? null) !== $order->currency || $invoiceMinor < $order->amount_minor) {
                Log::warning('Heleket: сумма/валюта не совпадает', ['order' => $order->public_id, 'data' => $data]);
                $this->orders->trackProviderStatus($order->public_id, 'amount_mismatch', $paidInfo);

                return;
            }
            $this->orders->confirmByProvider($order->public_id, $status, $paidInfo);

            return;
        }
        if (in_array($status, self::CLOSED, true)) {
            $this->orders->cancelByProvider($order->public_id, $status, __('payments.provider_closed'));

            return;
        }
        $this->orders->trackProviderStatus($order->public_id, $status, $status === 'wrong_amount' ? $paidInfo : null);
    }
}
