<?php

declare(strict_types=1);

namespace App\Modules\Payments\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManagePaymentSettings
{
    public static function minor(string $value): int
    {
        if (! preg_match('/^\d{1,7}(?:\.\d{1,2})?$/D', $value)) {
            throw ValidationException::withMessages(['amount' => 'Некорректная сумма.']);
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    public function execute(array $data): void
    {
        $price = self::minor((string) $data['diamond_price']);
        $minimum = self::minor((string) $data['minimum_amount']);
        if ($price < 1 || $price > 9999999 || $minimum < 1 || $minimum > $price * 30000) {
            throw ValidationException::withMessages(['diamond_price' => 'Курс должен быть положительным, а минимальная сумма достижима при покупке до 30 000 бриллиантов.']);
        }
        DB::transaction(function () use ($price, $minimum, $data): void {
            DB::table('payment_settings')->where('id', 1)->lockForUpdate()->first();
            DB::table('payment_settings')->where('id', 1)->update([
                'currency' => 'USD', 'diamond_price_minor' => $price, 'minimum_amount_minor' => $minimum,
                'is_enabled' => (bool) $data['is_enabled'], 'updated_at' => now(),
            ]);
        });
    }
}
