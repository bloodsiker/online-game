<?php

declare(strict_types=1);

namespace App\Modules\Commerce\Presentation\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class ShopPurchaseLimitInput
{
    public static function from(Request $request): array
    {
        $data = $request->validate([
            'purchase_limit_quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'purchase_limit_period_days' => ['nullable', 'numeric', 'min:0', 'max:36500'],
        ]);
        $quantity = (int) ($data['purchase_limit_quantity'] ?? 0);
        $seconds = (int) round((float) ($data['purchase_limit_period_days'] ?? 0) * 86400);
        if ($quantity > 0 && $seconds < 1) {
            throw ValidationException::withMessages(['purchase_limit_period_days' => 'Укажите период ограничения покупки.']);
        }

        return ['purchase_limit_quantity' => $quantity, 'purchase_limit_period_seconds' => $quantity > 0 ? $seconds : 0];
    }
}
