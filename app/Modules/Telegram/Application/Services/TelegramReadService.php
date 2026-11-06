<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Services;

use App\Modules\Structure\Auction\Domain\Contracts\OwnMarketListingsReader;
use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Domain\Contracts\CharacterSummaryReader;
use Illuminate\Support\Traits\Localizable;

final class TelegramReadService
{
    use Localizable;

    public function __construct(
        private readonly CharacterSummaryReader $characters,
        private readonly OwnMarketListingsReader $market,
        private readonly GameSettingsService $settings,
    ) {}

    public function locale(int $userId): string
    {
        return $this->settings->get(userId: $userId)['language'];
    }

    /** @return array{nickname: string, level: int, money: int, diamonds: string} */
    public function character(int $userId): array
    {
        return $this->characters->read(userId: $userId) ?? abort(403, 'Character unavailable.');
    }

    /** @return array{data: list<array<string, mixed>>, meta: array<string, int>} */
    public function listings(int $userId, string $type, int $page = 1, int $perPage = 10): array
    {
        // A revoked/unverified account must not retain access to its listings.
        $this->character(userId: $userId);

        return $this->withLocale($this->locale(userId: $userId), function () use ($userId, $type, $page, $perPage): array {
            $list = match ($type) {
                'auction' => $this->market->auction(userId: $userId, page: $page, perPage: $perPage),
                'exchange' => $this->market->exchange(userId: $userId, page: $page, perPage: $perPage),
            };

            return ['data' => $list->items(), 'meta' => [
                'current_page' => $list->currentPage(), 'last_page' => $list->lastPage(),
                'per_page' => $list->perPage(), 'total' => $list->total(),
            ]];
        });
    }
}
