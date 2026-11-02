<?php

declare(strict_types=1);

namespace App\Modules\Premium\Application\Services;

use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ExchangeDiamonds
{
    public function __construct(private readonly PremiumBonusService $bonuses) {}

    public static function cents(string $amount): int
    {
        if (! preg_match('/^\d{1,7}(?:\.\d{1,2})?$/D', $amount)) {
            throw ValidationException::withMessages(['amount' => __('premium.whole_diamonds')]);
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    public function execute(int $userId, string $amount, string $requestId): object
    {
        $cents = self::cents($amount);
        if ($cents < 100 || $cents % 100 !== 0) {
            throw ValidationException::withMessages(['amount' => __('premium.whole_diamonds')]);
        }

        return DB::transaction(function () use ($userId, $cents, $requestId): object {
            $user = DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            abort_unless($user, 404);
            $previous = DB::table('premium_exchanges')->where('user_id', $userId)->where('request_id', $requestId)->first();
            if ($previous) {
                return $previous;
            }
            $rate = (int) DB::table('premium_settings')->where('id', 1)->sharedLock()->value('gold_per_diamond');
            $thresholds = DB::table('premium_levels')->orderBy('level')->sharedLock()->pluck('required_diamond_cents')->all();
            if ($rate <= 0 || count($thresholds) !== 5 || in_array(null, $thresholds, true)) {
                throw ValidationException::withMessages(['amount' => __('premium.not_configured')]);
            }
            $gold = intdiv($cents * $rate, 100);
            if ($gold < 1 || $cents > (int) round((float) $user->diamond * 100)) {
                throw ValidationException::withMessages(['amount' => __('premium.insufficient_diamonds')]);
            }
            if ((int) $user->money + $gold > 2147483647) {
                throw ValidationException::withMessages(['amount' => __('premium.balance_limit')]);
            }
            $period = PremiumPeriod::query()->where('user_id', $userId)->where('expires_at', '>', now())
                ->orderByDesc('id')->lockForUpdate()->first();
            if (! $period) {
                $start = now();
                $period = PremiumPeriod::create(['user_id' => $userId, 'diamond_cents' => 0,
                    'started_at' => $start, 'expires_at' => $start->copy()->addDays(30)]);
            }
            $period->increment('diamond_cents', $cents);
            DB::table('users')->where('id', $userId)->update([
                'diamond' => DB::raw('diamond - '.intdiv($cents, 100)),
                'money' => DB::raw('money + '.$gold),
            ]);
            $balance = DB::table('users')->where('id', $userId)->first(['money', 'diamond']);
            $id = DB::table('premium_exchanges')->insertGetId([
                'user_id' => $userId, 'premium_period_id' => $period->id, 'request_id' => $requestId,
                'diamond_cents' => $cents, 'gold_per_diamond' => $rate, 'gold_amount' => $gold,
                'diamond_balance_after' => $balance->diamond, 'money_balance_after' => $balance->money,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->bonuses->forget($userId);

            return DB::table('premium_exchanges')->find($id);
        });
    }
}
