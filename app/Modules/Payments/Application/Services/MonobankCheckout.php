<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\Services;

use App\Modules\Payments\Domain\Enums\PaymentOrderStatus;
use App\Modules\Payments\Infrastructure\Monobank\MonobankClient;
use App\Modules\Payments\Infrastructure\Nbu\NbuExchangeRate;
use App\Modules\Payments\Infrastructure\Persistence\Models\PaymentOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

final class MonobankCheckout
{
    public const UAH = 980;

    private const CLOSED = ['failure', 'expired'];

    public function __construct(
        private readonly MonobankClient $client,
        private readonly PaymentOrderService $orders,
    ) {}

    /** Сумма списания в копейках по курсу НБУ на сегодня (ордер хранит сумму в USD-центах). */
    public static function chargeMinor(PaymentOrder $order, ?float $rate = null): int
    {
        return (int) round($order->amount_minor * ($rate ?? app(NbuExchangeRate::class)->usdToUah()));
    }

    public function paymentUrl(PaymentOrder $order): string
    {
        if ($order->provider !== 'monobank' || $order->status !== PaymentOrderStatus::PENDING) {
            throw ValidationException::withMessages(['order' => __('payments.already_processed')]);
        }
        if ($order->payment_url) {
            return $order->payment_url;
        }

        $rate = app(NbuExchangeRate::class)->usdToUah();
        $charge = self::chargeMinor($order, $rate);
        $title = __('payments.mono_destination', ['diamonds' => $order->diamonds]);
        try {
            $invoice = $this->client->createInvoice([
                'amount' => $charge,
                'ccy' => self::UAH,
                'merchantPaymInfo' => [
                    'reference' => $order->public_id,
                    'destination' => $title,
                    'basketOrder' => [[
                        'name' => $title, 'qty' => 1, 'sum' => $charge, 'total' => $charge,
                        'unit' => 'шт.', 'code' => 'diamonds-'.$order->diamonds,
                        'icon' => rtrim((string) config('app.url'), '/').'/main/images/data/bank_pack_picture/diamonds-3_200.png',
                    ]],
                ],
                'redirectUrl' => route('payments.orders.show', $order->public_id),
                'webHookUrl' => route('payments.monobank.webhook'),
                'validity' => (int) config('services.monobank.validity', 3600),
                'paymentType' => 'debit',
            ]);
        } catch (Throwable $e) {
            Log::error('Monobank: не удалось создать счёт', ['order' => $order->public_id, 'error' => $e->getMessage()]);
            throw ValidationException::withMessages(['order' => __('payments.provider_error')]);
        }

        $order->update([
            'provider_payment_id' => $invoice['invoiceId'], 'payment_url' => $invoice['pageUrl'],
            'charge_amount_minor' => $charge, 'charge_currency' => 'UAH', 'charge_rate' => $rate,
        ]);

        return $invoice['pageUrl'];
    }

    /** @param  array<string, mixed>  $data  тело вебхука = ответ «Статус рахунку» */
    public function handleWebhook(array $data): void
    {
        $invoiceId = $data['invoiceId'] ?? null;
        if (! is_string($invoiceId) || $invoiceId === '') {
            return;
        }
        $order = PaymentOrder::query()->where('provider', 'monobank')->where('provider_payment_id', $invoiceId)->first();
        if (! $order) {
            Log::warning('Monobank: вебхук для неизвестного счёта', ['invoiceId' => $invoiceId]);

            return;
        }
        if (isset($data['reference']) && $data['reference'] !== $order->public_id) {
            Log::warning('Monobank: reference не совпадает', ['order' => $order->public_id, 'reference' => $data['reference']]);

            return;
        }

        $status = (string) ($data['status'] ?? '');
        $info = 'Monobank: '.$status.', '.number_format(((int) ($data['finalAmount'] ?? $data['amount'] ?? 0)) / 100, 2, '.', ' ').' UAH'
            .(isset($data['failureReason']) ? ' — '.$data['failureReason'] : '');

        if ($status === 'success') {
            $amount = (int) ($data['amount'] ?? 0);
            if ((int) ($data['ccy'] ?? 0) !== self::UAH || $amount < (int) $order->charge_amount_minor) {
                Log::warning('Monobank: сумма/валюта не совпадает', ['order' => $order->public_id, 'data' => $data]);
                $this->orders->trackProviderStatus($order->public_id, 'amount_mismatch', $info);

                return;
            }
            $this->orders->confirmByProvider($order->public_id, $status, $info);

            return;
        }
        // Вебхуки приходят без гарантии порядка: оплаченный ордер промежуточными статусами не трогаем.
        if ($order->status !== PaymentOrderStatus::PENDING) {
            if ($status === 'reversed') {
                Log::warning('Monobank: возврат по оплаченному ордеру', ['order' => $order->public_id]);
                $this->orders->trackProviderStatus($order->public_id, $status, $info);
            }

            return;
        }
        if (in_array($status, self::CLOSED, true)) {
            $this->orders->cancelByProvider($order->public_id, $status, __('payments.provider_closed_mono'));

            return;
        }
        $this->orders->trackProviderStatus($order->public_id, $status);
    }
}
