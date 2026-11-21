<?php

declare(strict_types=1);

namespace App\Modules\Payments\Infrastructure\Persistence\Models;

use App\Modules\Payments\Domain\Enums\PaymentOrderStatus;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentOrder extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => PaymentOrderStatus::class,
        'amount_minor' => 'integer', 'charge_amount_minor' => 'integer', 'charge_rate' => 'float', 'diamonds' => 'integer', 'diamond_price_minor' => 'integer',
        'paid_at' => 'datetime', 'credited_at' => 'datetime', 'reviewed_at' => 'datetime',
    ];

    public function isOnline(): bool
    {
        return in_array($this->provider, ['heleket', 'monobank', 'trybit'], true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->without('player');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->without('player');
    }

    public function formattedAmount(): string
    {
        return number_format($this->amount_minor / 100, 2, '.', ' ').' '.$this->currency;
    }

    /** Сумма списания в валюте провайдера (гривна для monobank), если отличается от суммы ордера. */
    public function formattedCharge(): ?string
    {
        return $this->charge_amount_minor !== null && $this->charge_currency
            ? number_format($this->charge_amount_minor / 100, 2, '.', ' ').' '.$this->charge_currency
            : null;
    }
}
