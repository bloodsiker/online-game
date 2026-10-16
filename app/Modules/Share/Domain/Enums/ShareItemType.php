<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

enum ShareItemType: string
{
    case RESOURCE = 'resource';
    case FISH = 'fish';
    case PRECIOUS_GEM = 'precious_gem';
    case PLANT = 'plant';
    case WOOD = 'wood';
    case TOOL = 'tool';
    case WEAPON = 'weapon';
    case SHIELD = 'shield';
    case ARMOR = 'armor';
    case BELT = 'belt';
    case BAG = 'bag';
    case KEY = 'key';
    case LOCKPICK = 'lockpick';
    case TRADE_GOODS = 'trade_goods';
    case POTION = 'potion';
    case EAT = 'eat';
    case QUEST = 'quest';
    case ARTIFACT = 'artifact';
    case RECIPE = 'recipe';
    case BOOK = 'book';
    case CHEST = 'chest';
    case SCROLL = 'scroll';
    case GIFT = 'gift';
    case GEM = 'gem';
    case MOUNT = 'mount';
    case RUNE = 'rune';
    case RUNE_KEY = 'rune_key';
    case MISC = 'misc';

    /**
     * Экипировка занимает слоты и адресуется по конкретному item_id,
     * поэтому каждый экземпляр — отдельный предмет (не стакается).
     */
    public function isEquipment(): bool
    {
        return in_array($this, [
            self::TOOL,
            self::WEAPON,
            self::SHIELD,
            self::ARMOR,
            self::BELT,
            self::BAG,
            self::ARTIFACT,
        ], true);
    }

    public function isGatheringResource(): bool
    {
        return in_array($this, self::gatheringResources(), true);
    }

    /** @return list<self> */
    public static function gatheringResources(): array
    {
        return [
            self::RESOURCE,
            self::FISH,
            self::PRECIOUS_GEM,
            self::PLANT,
            self::WOOD,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::RESOURCE => __('Ресурсы'),
            self::FISH => __('Рыба'),
            self::PRECIOUS_GEM => __('Драгоценные камни'),
            self::PLANT => __('Растения'),
            self::WOOD => __('Дерево'),
            self::TOOL => __('Инструмент'),
            self::WEAPON => __('Оружие'),
            self::SHIELD => __('Щит'),
            self::ARMOR => __('Броня'),
            self::BELT => __('Пояс'),
            self::BAG => __('Сумка'),
            self::KEY => __('Ключ'),
            self::LOCKPICK => __('Отмычка'),
            self::TRADE_GOODS => __('Торговый товар'),
            self::POTION => __('Эликсир'),
            self::SCROLL => __('Свиток'),
            self::QUEST => __('Квест'),
            self::ARTIFACT => __('Артефакт'),
            self::RECIPE => __('Рецепт'),
            self::BOOK => __('Книга заклинаний'),
            self::CHEST => __('Сундук'),
            self::EAT => __('Еда'),
            self::GIFT => __('Подарок'),
            self::GEM => __('Камень'),
            self::MOUNT => __('Оправа'),
            self::RUNE => __('Руна'),
            self::RUNE_KEY => __('Рунный ключ'),
            self::MISC => __('Разное'),
        };
    }

    public static function group(string $group): array
    {
        return match ($group) {
            'main' => [
                self::POTION,
                self::TOOL,
                self::WEAPON,
                self::SHIELD,
                self::ARMOR,
                self::BELT,
                self::BAG,
                self::RESOURCE,
                self::FISH,
                self::PRECIOUS_GEM,
                self::PLANT,
                self::WOOD,
                self::RECIPE,
                self::BOOK,
                self::CHEST,
                self::LOCKPICK,
                self::TRADE_GOODS,
                self::SCROLL,
                self::GEM,
                self::MOUNT,
                self::RUNE,
                self::RUNE_KEY,
                self::EAT,
            ],
            'key' => [self::KEY],
            'quest' => [self::QUEST],
            'artifact' => [self::ARTIFACT],
            'gift' => [self::GIFT],
            'misc' => [self::MISC],
            default => self::group('main'),
        };
    }

    public static function values(array $cases): array
    {
        return array_map(fn (self $type) => $type->value, $cases);
    }
}
