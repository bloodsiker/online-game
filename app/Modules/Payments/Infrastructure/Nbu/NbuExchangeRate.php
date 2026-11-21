<?php

declare(strict_types=1);

namespace App\Modules\Payments\Infrastructure\Nbu;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Официальный курс НБУ (грн за 1 USD). Загружается раз в сутки ночной задачей планировщика
 * в таблицу exchange_rates; при оплате берётся последний сохранённый курс — без запросов к НБУ.
 */
final class NbuExchangeRate
{
    public const SOURCE = 'nbu';

    private const CURRENT_CACHE = 'payments:nbu:usd:current';

    /** Загрузить курс на дату (по умолчанию — сегодня по Киеву) и сохранить в историю. */
    public function sync(?CarbonImmutable $date = null): float
    {
        $date ??= CarbonImmutable::now('Europe/Kyiv');
        $response = Http::timeout(10)->retry(3, 1000, throw: false)->acceptJson()->get((string) config('services.monobank.nbu_url'), [
            'valcode' => 'USD', 'date' => $date->format('Ymd'), 'json' => '',
        ]);
        $rate = $response->json('0.rate');
        if (! $response->successful() || ! is_numeric($rate) || (float) $rate <= 1) {
            throw new RuntimeException('НБУ: не удалось получить курс USD — HTTP '.$response->status().' '.mb_substr($response->body(), 0, 300));
        }
        $rate = round((float) $rate, 4);

        DB::table('exchange_rates')->updateOrInsert(
            ['rate_date' => $date->toDateString(), 'currency' => 'USD', 'source' => self::SOURCE],
            ['rate' => $rate, 'created_at' => now(), 'updated_at' => now()],
        );
        Cache::forget(self::CURRENT_CACHE);

        return $rate;
    }

    /** Курс для оплаты: последний сохранённый на сегодня или раньше; без истории — запасной из конфига. */
    public function usdToUah(): float
    {
        $rate = Cache::remember(self::CURRENT_CACHE, 600, fn () => $this->current()?->rate);

        return is_numeric($rate) && (float) $rate > 0
            ? (float) $rate
            : (float) config('services.monobank.uah_per_usd', 42);
    }

    public function current(): ?object
    {
        return DB::table('exchange_rates')->where('currency', 'USD')->where('source', self::SOURCE)
            ->where('rate_date', '<=', CarbonImmutable::now('Europe/Kyiv')->toDateString())
            ->orderByDesc('rate_date')->first();
    }

    /** @return Collection<int, object> */
    public function history(int $days = 30): Collection
    {
        return DB::table('exchange_rates')->where('currency', 'USD')->where('source', self::SOURCE)
            ->orderByDesc('rate_date')->limit($days)->get();
    }
}
