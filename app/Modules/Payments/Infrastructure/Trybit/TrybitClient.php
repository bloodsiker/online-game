<?php

declare(strict_types=1);

namespace App\Modules\Payments\Infrastructure\Trybit;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Клиент Trybit (https://docs.trybit.com/ru): заголовок «Authorization: Token <API KEY>».
 * POSTBACK несёт JWT (HS256, SECRET KEY, живёт 5 минут); сам токен не подписывает тело,
 * поэтому перед начислением счёт перепроверяется через /v2/invoice/merchant/info.
 */
final class TrybitClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.trybit.api_key'))
            && filled(config('services.trybit.shop_id'))
            && filled(config('services.trybit.secret_key'));
    }

    /**
     * @param  array<string, mixed>  $payload  без shop_id — он подставляется из конфига
     * @return array{uuid: string, link: string}
     */
    public function createInvoice(array $payload): array
    {
        $response = $this->http()->post($this->url('/v2/invoice/create'), ['shop_id' => (string) config('services.trybit.shop_id')] + $payload);
        $uuid = $response->json('result.uuid');
        $link = $response->json('result.link');
        if (! $response->successful() || $response->json('status') !== 'success' || ! is_string($uuid) || ! is_string($link) || $link === '') {
            throw new RuntimeException('Trybit invoice error: HTTP '.$response->status().' '.mb_substr($response->body(), 0, 500));
        }

        return ['uuid' => $uuid, 'link' => str_starts_with($link, 'http') ? $link : 'https://'.ltrim($link, '/')];
    }

    /** @return array<string, mixed>|null */
    public function invoiceInfo(string $uuid): ?array
    {
        $response = $this->http()->post($this->url('/v2/invoice/merchant/info'), ['uuids' => [$uuid]]);
        $info = $response->json('result.0');
        if (! $response->successful() || $response->json('status') !== 'success' || ! is_array($info)) {
            throw new RuntimeException('Trybit info error: HTTP '.$response->status().' '.mb_substr($response->body(), 0, 500));
        }

        return self::normalizeUuid((string) ($info['uuid'] ?? '')) === self::normalizeUuid($uuid) ? $info : null;
    }

    /** JWT HS256 из POSTBACK: подпись SECRET KEY и срок действия. */
    public function verifyToken(?string $token): bool
    {
        $secret = (string) config('services.trybit.secret_key');
        if ($secret === '' || ! is_string($token) || substr_count($token, '.') !== 2) {
            return false;
        }
        [$header, $payload, $signature] = explode('.', trim($token));
        $headerData = json_decode((string) self::base64UrlDecode($header), true);
        $payloadData = json_decode((string) self::base64UrlDecode($payload), true);
        if (! is_array($headerData) || ($headerData['alg'] ?? null) !== 'HS256' || ! is_array($payloadData)) {
            return false;
        }
        $expected = hash_hmac('sha256', $header.'.'.$payload, $secret, true);
        $given = self::base64UrlDecode($signature);
        if ($given === null || ! hash_equals($expected, $given)) {
            return false;
        }
        $now = time();
        if (isset($payloadData['exp']) && is_numeric($payloadData['exp']) && $now >= (int) $payloadData['exp'] + 30) {
            return false;
        }

        return ! (isset($payloadData['nbf']) && is_numeric($payloadData['nbf']) && $now + 30 < (int) $payloadData['nbf']);
    }

    /** «INV-89UX09KA» и «89UX09KA» — один и тот же счёт. */
    public static function normalizeUuid(string $uuid): string
    {
        return strtoupper((string) preg_replace('/^INV-/i', '', trim($uuid)));
    }

    private static function base64UrlDecode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        return $decoded === false ? null : $decoded;
    }

    private function http(): PendingRequest
    {
        return Http::timeout(15)->acceptJson()->withHeaders(['Authorization' => 'Token '.config('services.trybit.api_key')]);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.trybit.base_url'), '/').$path;
    }
}
