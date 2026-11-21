<?php

declare(strict_types=1);

namespace App\Modules\Payments\Infrastructure\Monobank;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Клиент plata by mono: заголовок X-Token. Вебхук подписан ECDSA (SHA-256) — X-Sign (base64)
 * от сырого тела, публичный ключ берётся из /api/merchant/pubkey и кешируется.
 */
final class MonobankClient
{
    private const PUBKEY_CACHE = 'payments:monobank:pubkey';

    public function isConfigured(): bool
    {
        return filled(config('services.monobank.token'));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{invoiceId: string, pageUrl: string}
     */
    public function createInvoice(array $payload): array
    {
        $response = $this->http()->post($this->url('/api/merchant/invoice/create'), $payload);
        $invoiceId = $response->json('invoiceId');
        $pageUrl = $response->json('pageUrl');
        if (! $response->successful() || ! is_string($invoiceId) || ! is_string($pageUrl) || $pageUrl === '') {
            throw new RuntimeException('Monobank invoice error: HTTP '.$response->status().' '.mb_substr($response->body(), 0, 500));
        }

        return ['invoiceId' => $invoiceId, 'pageUrl' => $pageUrl];
    }

    public function verifyWebhook(string $body, ?string $xSign): bool
    {
        if (! $this->isConfigured() || ! is_string($xSign) || $xSign === '') {
            return false;
        }
        $signature = base64_decode($xSign, true);
        if ($signature === false) {
            return false;
        }
        if ($this->verifyWith($this->publicKey(), $body, $signature)) {
            return true;
        }

        // Ключ мог смениться — перечитываем один раз.
        return $this->verifyWith($this->publicKey(refresh: true), $body, $signature);
    }

    private function verifyWith(?string $pem, string $body, string $signature): bool
    {
        if ($pem === null) {
            return false;
        }
        $key = openssl_pkey_get_public($pem);

        return $key !== false && openssl_verify($body, $signature, $key, OPENSSL_ALGO_SHA256) === 1;
    }

    private function publicKey(bool $refresh = false): ?string
    {
        if ($refresh) {
            Cache::forget(self::PUBKEY_CACHE);
        }

        $pem = Cache::remember(self::PUBKEY_CACHE, now()->addDay(), function (): string {
            $key = $this->http()->get($this->url('/api/merchant/pubkey'))->json('key');

            return is_string($key) ? (string) base64_decode($key, true) : '';
        });

        if (! is_string($pem) || $pem === '') {
            Cache::forget(self::PUBKEY_CACHE);

            return null;
        }

        return $pem;
    }

    private function http(): PendingRequest
    {
        return Http::timeout(15)->acceptJson()->withHeaders(['X-Token' => (string) config('services.monobank.token')]);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.monobank.base_url'), '/').$path;
    }
}
