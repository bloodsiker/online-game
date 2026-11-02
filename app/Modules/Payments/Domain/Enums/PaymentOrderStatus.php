<?php

declare(strict_types=1);

namespace App\Modules\Payments\Domain\Enums;

enum PaymentOrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return __('payments.status.'.$this->value);
    }
}
