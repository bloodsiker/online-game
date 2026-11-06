<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Services;

use App\Modules\Telegram\Domain\Contracts\BackpackPreviewRenderer;
use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;
use App\Modules\User\Domain\Contracts\CharacterSummaryReader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

final class TelegramBackpackPreview
{
    public function __construct(
        private readonly BackpackPreviewRenderer $renderer,
        private readonly TelegramLinkRepository $links,
        private readonly CharacterSummaryReader $characters,
    ) {}

    /** @param list<array<string, mixed>> $items */
    public function create(int $userId, int $telegramId, array $items): string
    {
        // Repeated refreshes of unchanged contents reuse the same immutable picture.
        $token = substr(hash_hmac('sha256', json_encode([$userId, $telegramId, $items], JSON_THROW_ON_ERROR), (string) config('app.key')), 0, 48);
        $expiry = now()->addDay();
        $key = 'telegram.backpack.preview.'.$token;
        $preview = Cache::get($key) ?? [
            'user_id' => $userId, 'telegram_id' => $telegramId, 'png' => $this->renderer->render(items: $items),
        ];
        $ownerKey = 'telegram.backpack.previews.'.$userId.'.'.$telegramId;
        Cache::lock($ownerKey.'.lock', 5)->block(2, function () use ($ownerKey, $token, $key, $preview, $expiry): void {
            $tokens = array_values(array_diff(Cache::get($ownerKey, []), [$token]));
            $tokens[] = $token;
            while (count($tokens) > 64) {
                Cache::forget('telegram.backpack.preview.'.array_shift($tokens));
            }
            Cache::put($key, $preview, $expiry);
            Cache::put($ownerKey, $tokens, $expiry);
        });

        return URL::temporarySignedRoute('telegram.backpack.preview', $expiry, ['token' => $token]);
    }

    public function image(string $token): ?string
    {
        $preview = Cache::get('telegram.backpack.preview.'.$token);
        if (! is_array($preview)) {
            return null;
        }
        $link = $this->links->forTelegram(telegramUserId: $preview['telegram_id']);
        if ($link?->userId !== $preview['user_id'] || ! $this->characters->read(userId: $preview['user_id'])) {
            return null;
        }

        return $preview['png'];
    }
}
