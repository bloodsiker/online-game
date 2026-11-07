<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Services;

use App\Modules\Backpack\Domain\Enums\BackpackCategory;
use App\Modules\Backpack\Domain\Repositories\BackpackReadRepository;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Auction\Domain\Contracts\AuctionListingsReader;
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
        private readonly BackpackReadRepository $backpack,
        private readonly AuctionListingsReader $auction,
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

    /** @return array{category: string, categories: list<array<string, mixed>>, data: list<array<string, mixed>>, meta: array<string, int>} */
    public function backpack(int $userId, BackpackCategory $category = BackpackCategory::MAIN, int $page = 1, int $perPage = 8): array
    {
        $this->character(userId: $userId);

        return $this->withLocale($this->locale(userId: $userId), fn (): array => $this->backpack->page(
            userId: $userId, category: $category, page: $page, perPage: $perPage,
        ));
    }

    /** @return array{categories: list<array<string, mixed>>, data: list<array<string, mixed>>, meta: array{total: int}} */
    public function allBackpackItems(int $userId): array
    {
        $this->character(userId: $userId);

        return $this->withLocale($this->locale(userId: $userId), fn (): array => $this->backpack->all(userId: $userId));
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

    /** @return array<string, mixed> */
    public function auction(int $userId, string $scope = 'all', int $page = 1, string $name = '', ?ShareItemType $type = null, ?ItemRarity $rarity = null): array
    {
        $this->character(userId: $userId);

        return $this->withLocale($this->locale(userId: $userId), function () use ($userId, $scope, $page, $name, $type, $rarity): array {
            $list = $this->auction->page(page: $page, perPage: 20, ownerId: $scope === 'mine' ? $userId : null,
                name: $name, type: $type, rarity: $rarity);

            return ['scope' => $scope, 'data' => $list->items(), 'meta' => [
                'current_page' => $list->currentPage(), 'last_page' => $list->lastPage(),
                'per_page' => $list->perPage(), 'total' => $list->total(),
            ], 'filters' => [
                'types' => array_map(static fn (ShareItemType $value): array => ['value' => $value->value, 'label' => $value->label()], ShareItemType::cases()),
                'rarities' => array_map(static fn (ItemRarity $value): array => ['value' => $value->value, 'label' => __($value->label())], ItemRarity::cases()),
            ]];
        });
    }
}
