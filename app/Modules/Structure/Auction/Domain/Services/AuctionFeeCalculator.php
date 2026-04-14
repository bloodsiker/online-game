<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Domain\Services;

class AuctionFeeCalculator
{
    public function calculate(int $price, float $rate = 1.0): int
    {
        if ($price <= 0) {
            return 0;
        }

        $fee = pow(0.5, log10($price) + 2) * $price * $rate;
        $fee = (int) ceil($fee);

        return $fee <= 0 ? 0 : $fee;
    }
}