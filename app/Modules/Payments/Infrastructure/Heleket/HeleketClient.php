<?php

declare(strict_types=1);

namespace App\Modules\Payments\Infrastructure\Heleket;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Клиент Heleket: подпись = md5(base64(json_body) . api_key), заголовки merchant + sign.
 * Тело отправляется ровно той строкой, что подписана (json_encode экранирует «/» — так же делает Heleket).
 */
final class HeleketClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.heleket.merchant')) && filled(config('services.heleket.api_key'));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed> result из ответа
     */
    public function createInvoice(array $payload): array
    {
        $body = (string) json_encode($payload);
        $response = Http::timeout(15)
            ->withHeaders(['merchant' => (string) config('services.heleket.merchant'), 'sign' => $this->sign($body)])
            ->withBody($body, 'application/json')
            ->post(rtrim((string) config('services.heleket.base_url'), '/').'/v1/payment');

        $result = $response->json('result');
        if (! $response->successful() || (int) $response->json('state', 1) !== 0 || ! is_array($result) || empty($result['url'])) {
            throw new RuntimeException('Heleket invoice error: HTTP '.$response->status().' '.mb_substr($response->body(), 0, 500));
        }

        return $result;
    }

    /** @param  array<string, mixed>  $data  тело вебхука вместе с полем sign */
    public function verifyWebhook(array $data): bool
    {
        $received = $data['sign'] ?? null;
        if (! is_string($received) || ! $this->isConfigured()) {
            return false;
        }
        unset($data['sign']);

        return hash_equals($this->sign((string) json_encode($data, JSON_UNESCAPED_UNICODE)), $received);
    }

    public function sign(string $body): string
    {
        return md5(base64_encode($body).config('services.heleket.api_key'));
    }
}
