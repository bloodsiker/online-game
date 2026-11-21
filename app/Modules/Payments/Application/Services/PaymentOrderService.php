<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\Services;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Notification\Application\Services\PlayerNotificationService;
use App\Modules\Payments\Domain\Enums\PaymentOrderStatus;
use App\Modules\Payments\Infrastructure\Heleket\HeleketClient;
use App\Modules\Payments\Infrastructure\Persistence\Models\PaymentOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PaymentOrderService
{
    public function __construct(
        private readonly PlayerNotificationService $notifications,
        private readonly HeleketClient $heleket,
    ) {}

    public function create(int $userId, int $diamonds, string $requestId): PaymentOrder
    {
        if ($diamonds < 1 || $diamonds > 30000 || ! Str::isUuid($requestId)) {
            throw ValidationException::withMessages(['diamonds' => __('payments.invalid_quantity')]);
        }

        return DB::transaction(function () use ($userId, $diamonds, $requestId): PaymentOrder {
            abort_unless(DB::table('users')->where('id', $userId)->lockForUpdate()->first(), 404);
            $existing = PaymentOrder::query()->where('user_id', $userId)->where('request_id', $requestId)->first();
            if ($existing) {
                return $existing;
            }
            $settings = DB::table('payment_settings')->where('id', 1)->sharedLock()->first();
            if (! $settings || ! $settings->is_enabled || $settings->diamond_price_minor < 1 || ($settings->currency ?? null) !== 'USD') {
                throw ValidationException::withMessages(['diamonds' => __('payments.disabled')]);
            }
            $amount = $diamonds * (int) $settings->diamond_price_minor;
            if ($amount < $settings->minimum_amount_minor) {
                throw ValidationException::withMessages(['diamonds' => __('payments.minimum', ['amount' => number_format($settings->minimum_amount_minor / 100, 2, '.', ' ')])]);
            }
            if (PaymentOrder::query()->where('user_id', $userId)->where('status', PaymentOrderStatus::PENDING)->count() >= 5) {
                throw ValidationException::withMessages(['diamonds' => __('payments.too_many_pending')]);
            }
            $order = PaymentOrder::create([
                'public_id' => (string) Str::uuid(), 'user_id' => $userId, 'request_id' => $requestId,
                'status' => PaymentOrderStatus::PENDING, 'provider' => $this->heleket->isConfigured() ? 'heleket' : 'manual', 'currency' => $settings->currency,
                'amount_minor' => $amount, 'diamond_price_minor' => $settings->diamond_price_minor, 'diamonds' => $diamonds,
            ]);
            $this->event($order, $userId, null, PaymentOrderStatus::PENDING);

            return $order;
        });
    }

    public function confirm(string $publicId, int $adminId, ?string $note = null): PaymentOrder
    {
        $this->assertAdmin($adminId);

        return $this->credit($publicId, $adminId, $note, [PaymentOrderStatus::PENDING]);
    }

    /**
     * Оплата подтверждена платёжной системой (вебхук). Ордер, отменённый игроком, но всё-таки
     * оплаченный, тоже зачисляется — деньги получены. Отклонённый администратором — нет.
     */
    public function confirmByProvider(string $publicId, string $providerStatus, ?string $note = null): PaymentOrder
    {
        return $this->credit($publicId, null, $note, [PaymentOrderStatus::PENDING, PaymentOrderStatus::CANCELLED], $providerStatus);
    }

    /** Счёт просрочен/отменён на стороне платёжной системы — закрываем ожидающий ордер. */
    public function cancelByProvider(string $publicId, string $providerStatus, string $note): PaymentOrder
    {
        return DB::transaction(function () use ($publicId, $providerStatus, $note): PaymentOrder {
            $order = PaymentOrder::query()->where('public_id', $publicId)->lockForUpdate()->firstOrFail();
            $order->update(['provider_status' => $providerStatus]);
            if ($order->status !== PaymentOrderStatus::PENDING) {
                return $order;
            }
            $order->update(['status' => PaymentOrderStatus::CANCELLED, 'review_note' => $note, 'reviewed_at' => now()]);
            $this->event($order, null, PaymentOrderStatus::PENDING, PaymentOrderStatus::CANCELLED, $note);

            return $order;
        });
    }

    /** Промежуточный статус провайдера (confirm_check, wrong_amount…) — только фиксируем, без начисления. */
    public function trackProviderStatus(string $publicId, string $providerStatus, ?string $note = null): void
    {
        PaymentOrder::query()->where('public_id', $publicId)->update(array_filter([
            'provider_status' => $providerStatus, 'review_note' => $note, 'updated_at' => now(),
        ], fn ($value) => $value !== null));
    }

    /** @param  list<PaymentOrderStatus>  $allowedFrom */
    private function credit(string $publicId, ?int $actorId, ?string $note, array $allowedFrom, ?string $providerStatus = null): PaymentOrder
    {
        $ownerId = PaymentOrder::query()->where('public_id', $publicId)->value('user_id');
        abort_unless($ownerId, 404);

        return DB::transaction(function () use ($publicId, $actorId, $note, $ownerId, $allowedFrom, $providerStatus): PaymentOrder {
            // Общий порядок блокировок с обменом и торговлей: сначала кошелёк, затем ордер.
            $user = DB::table('users')->where('id', $ownerId)->lockForUpdate()->first();
            $order = PaymentOrder::query()->where('public_id', $publicId)->lockForUpdate()->firstOrFail();
            if ($order->status === PaymentOrderStatus::PAID && $order->credited_at !== null) {
                return $order;
            }
            if (! in_array($order->status, $allowedFrom, true)) {
                throw ValidationException::withMessages(['order' => __('payments.already_processed')]);
            }
            if (! $user || (int) $user->diamond + $order->diamonds > 2147483647) {
                throw ValidationException::withMessages(['order' => __('payments.balance_limit')]);
            }
            $from = $order->status;
            DB::table('users')->where('id', $ownerId)->increment('diamond', $order->diamonds);
            $balance = (int) DB::table('users')->where('id', $ownerId)->value('diamond');
            $order->update(array_filter([
                'status' => PaymentOrderStatus::PAID, 'reviewed_by' => $actorId, 'review_note' => $note,
                'reviewed_at' => now(), 'paid_at' => now(), 'credited_at' => now(), 'diamond_balance_after' => $balance,
                'provider_status' => $providerStatus,
            ], fn ($value) => $value !== null));
            $this->event($order, $actorId, $from, PaymentOrderStatus::PAID, $note);
            $this->notifications->notify(
                userId: (int) $ownerId, type: 'payment_credited', deduplicationKey: 'payment_order:'.$order->id,
                message: new LocalizedChatMessage('payments.credited_notification', ['order' => $order->public_id, 'diamonds' => $order->diamonds]),
            );

            return $order;
        });
    }

    public function reject(string $publicId, int $adminId, string $note): PaymentOrder
    {
        $this->assertAdmin($adminId);
        if (trim($note) === '') {
            throw ValidationException::withMessages(['note' => __('payments.rejection_reason')]);
        }

        return $this->close($publicId, $adminId, PaymentOrderStatus::REJECTED, $note);
    }

    public function cancel(string $publicId, int $userId): PaymentOrder
    {
        abort_unless(PaymentOrder::query()->where('public_id', $publicId)->where('user_id', $userId)->exists(), 404);

        return $this->close($publicId, $userId, PaymentOrderStatus::CANCELLED);
    }

    private function close(string $publicId, int $actorId, PaymentOrderStatus $status, ?string $note = null): PaymentOrder
    {
        return DB::transaction(function () use ($publicId, $actorId, $status, $note): PaymentOrder {
            $order = PaymentOrder::query()->where('public_id', $publicId)->lockForUpdate()->firstOrFail();
            if ($order->status === $status) {
                return $order;
            }
            $this->assertPending($order);
            $order->update([
                'status' => $status, 'review_note' => $note,
                'reviewed_by' => $status === PaymentOrderStatus::REJECTED ? $actorId : null,
                'reviewed_at' => now(),
            ]);
            $this->event($order, $actorId, PaymentOrderStatus::PENDING, $status, $note);

            return $order;
        });
    }

    private function assertPending(PaymentOrder $order): void
    {
        if ($order->status !== PaymentOrderStatus::PENDING) {
            throw ValidationException::withMessages(['order' => __('payments.already_processed')]);
        }
    }

    private function assertAdmin(int $adminId): void
    {
        abort_unless((bool) DB::table('users')->where('id', $adminId)->value('is_admin'), 403);
    }

    private function event(PaymentOrder $order, ?int $actorId, ?PaymentOrderStatus $from, PaymentOrderStatus $to, ?string $note = null): void
    {
        DB::table('payment_order_events')->insert([
            'payment_order_id' => $order->id, 'actor_id' => $actorId, 'from_status' => $from?->value,
            'to_status' => $to->value, 'note' => $note, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
