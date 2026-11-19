<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

use App\Modules\Share\Domain\Enums\ItemRarity;

/** Фильтры витрины магазина (правая панель). */
final readonly class ShopFilterDTO
{
    public const LEVEL_ANY = 'any';

    public const LEVEL_MINE = 'mine';

    public const LEVEL_RANGE = 'range';

    public const SORT_DEFAULT = 'default';

    public const SORT_PRICE_ASC = 'price_asc';

    public const SORT_PRICE_DESC = 'price_desc';

    public const SORT_LEVEL_ASC = 'level_asc';

    public const SORT_LEVEL_DESC = 'level_desc';

    public const SORT_RARITY_DESC = 'rarity_desc';

    public const SORTS = [
        self::SORT_DEFAULT => 'по умолчанию',
        self::SORT_PRICE_ASC => 'сначала дешёвые',
        self::SORT_PRICE_DESC => 'сначала дорогие',
        self::SORT_LEVEL_ASC => 'уровень ↑',
        self::SORT_LEVEL_DESC => 'уровень ↓',
        self::SORT_RARITY_DESC => 'сначала редкие',
    ];

    /**
     * @param  list<string>  $rarities  значения ItemRarity; пусто — любые
     */
    public function __construct(
        public string $search = '',
        public bool $availableOnly = false,
        public string $levelMode = self::LEVEL_ANY,
        public ?int $levelFrom = null,
        public ?int $levelTo = null,
        public array $rarities = [],
        public string $sort = self::SORT_DEFAULT,
    ) {}

    public function isActive(): bool
    {
        return $this->search !== ''
            || $this->availableOnly
            || $this->levelMode !== self::LEVEL_ANY
            || ($this->rarities !== [] && count($this->rarities) < count(ItemRarity::cases()))
            || $this->sort !== self::SORT_DEFAULT;
    }

    public function allowsRarity(string $rarity): bool
    {
        return $this->rarities === [] || in_array($rarity, $this->rarities, true);
    }
}
