<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\Services;

use App\Modules\Payments\Domain\Enums\PaymentOrderStatus;
use App\Modules\Payments\Infrastructure\Persistence\Models\PaymentOrder;
use App\Modules\Payments\Infrastructure\Trybit\TrybitClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

final class TrybitCheckout
{
    private const PAID = ['paid', 'overpaid'];

    public function __construct(
        private readonly TrybitClient $client,
        private readonly PaymentOrderService $orders,
    ) {}

    public function paymentUrl(PaymentOrder $order): string
    {
        if ($order->provider !== 'trybit' || $order->status !== PaymentOrderStatus::PENDING) {
            throw ValidationException::withMessages(['order' => __('payments.already_processed')]);
        }
        if ($order->payment_url) {
            return $order->payment_url;
        }

        $minutes = max(15, (int) config('services.trybit.lifetime_minutes', 60));
        try {
            $invoice = $this->client->createInvoice([
                'amount' => round($order->amount_minor / 100, 2),
                'currency' => $order->currency,
                'order_id' => $order->public_id,
                'add_fields' => ['time_to_pay' => ['hours' => intdiv($minutes, 60), 'minutes' => $minutes % 60]],
            ]);
        } catch (Throwable $e) {
            Log::error('Trybit: не удалось создать счёт', ['order' => $order->public_id, 'error' => $e->getMessage()]);
            throw ValidationException::withMessages(['order' => __('payments.provider_error')]);
        }

        $order->update(['provider_payment_id' => $invoice['uuid'], 'payment_url' => $invoice['link']]);

        return $invoice['link'];
    }

    /**
     * POSTBACK уже прошёл проверку JWT. Данным уведомления не доверяем — статус и сумму
     * берём из API Trybit по номеру счёта.
     *
     * @param  array<string, mixed>  $data
     */
    public function handlePostback(array $data): void
    {
        $invoiceId = $data['invoice_id'] ?? null;
        if (! is_string($invoiceId) || trim($invoiceId) === '') {
            return;
        }
        $uuid = 'INV-'.TrybitClient::normalizeUuid($invoiceId);
        $order = PaymentOrder::query()->where('provider', 'trybit')->whereIn('provider_payment_id', [$uuid, TrybitClient::normalizeUuid($invoiceId)])->first();
        if (! $order) {
            Log::warning('Trybit: POSTBACK для неизвестного счёта', ['invoice_id' => $invoiceId]);

            return;
        }

        $info = $this->client->invoiceInfo($uuid);
        if ($info === null) {
            Log::warning('Trybit: счёт не найден в API', ['order' => $order->public_id, 'invoice' => $uuid]);

            return;
        }
        if (isset($info['order_id']) && is_string($info['order_id']) && ! in_array($info['order_id'], ['', 'None', $order->public_id], true)) {
            Log::warning('Trybit: order_id не совпадает', ['order' => $order->public_id, 'info_order' => $info['order_id']]);

            return;
        }

        $status = strtolower((string) ($info['status'] ?? ''));
        $note = sprintf('Trybit: %s, %s %s (%s USD)', $status, $info['amount_paid'] ?? '?', $info['currency']['code'] ?? '', $info['amount_paid_usd'] ?? '?');

        if (($info['invoice_status'] ?? null) === 'success' && in_array($status, self::PAID, true)) {
            // Счёт выставлен в валюте ордера — сверяем фиатную сумму счёта с ордером.
            $fiatMinor = (int) round(((float) ($info['amount_in_fiat'] ?? $info['amount_usd'] ?? 0)) * 100);
            $fiatCurrency = strtoupper((string) ($info['fiat_currency'] ?? $order->currency));
            if ($fiatCurrency !== $order->currency || $fiatMinor < $order->amount_minor) {
                Log::warning('Trybit: сумма/валюта не совпадает', ['order' => $order->public_id, 'info' => $info]);
                $this->orders->trackProviderStatus($order->public_id, 'amount_mismatch', $note);

                return;
            }
            $this->orders->confirmByProvider($order->public_id, $status, $note);

            return;
        }
        if ($order->status !== PaymentOrderStatus::PENDING) {
            return;
        }
        if (str_contains($status, 'cancel') || str_contains($status, 'expire')) {
            $this->orders->cancelByProvider($order->public_id, $status, __('payments.provider_closed_trybit'));

            return;
        }
        // Недоплата и прочие промежуточные статусы — только фиксируем, ордер остаётся на проверке.
        $this->orders->trackProviderStatus($order->public_id, $status !== '' ? mb_substr($status, 0, 32) : 'unknown', $note);
    }
}
