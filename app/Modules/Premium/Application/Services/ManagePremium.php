<?php

declare(strict_types=1);

namespace App\Modules\Premium\Application\Services;

use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumLevel;
use Illuminate\Support\Facades\DB;

final class ManagePremium
{
    public function execute(array $data): void
    {
        DB::transaction(function () use ($data): void {
            DB::table('premium_settings')->where('id', 1)->lockForUpdate()->first();
            DB::table('premium_settings')->where('id', 1)->update(['gold_per_diamond' => $data['gold_per_diamond']]);
            foreach (range(1, 5) as $level) {
                $config = PremiumLevel::query()->whereKey($level)->lockForUpdate()->firstOrFail();
                $config->update([
                    'required_diamond_cents' => ExchangeDiamonds::cents((string) $data['levels'][$level]['threshold']),
                    'bonuses' => array_merge($data['levels'][$level]['bonuses'], [
                        '_enabled' => $data['levels'][$level]['bonus_enabled'] ?? [],
                    ]),
                ]);
            }
        });
    }
}
