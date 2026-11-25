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
    case HELMET = 'helmet';
    case SHOULDER = 'shoulder';
    case FOREARM = 'forearm';
    case LEGGING = 'legging';
    case CHAIN_ARMOR = 'chain_armor';
    case CLOAK = 'cloak';
    case SHOES = 'shoes';
    case GLOVES = 'gloves';
    case BELT = 'belt';
    case BAG = 'bag';
    case RING = 'ring';
    case EARRING = 'earring';
    case NECKLACE = 'necklace';
    case QUIVER = 'quiver';
    case ARROW = 'arrow';
    case KEY = 'key';
    case LOCKPICK = 'lockpick';
    case TRADE_GOODS = 'trade_goods';
    case POTION = 'potion';
    case EAT = 'eat';
    case QUEST = 'quest';
    case ARTIFACT = 'artifact';
    case RECIPE = 'recipe';
    case BOOK = 'book';
    case BOSS_MANUAL = 'boss_manual';
    case CHEST = 'chest';
    case SCROLL = 'scroll';
    case TREASURE_MAP = 'treasure_map';
    case TREASURE_MAP_FRAGMENT = 'treasure_map_fragment';
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
            ...self::armorTypes(),
            self::BELT,
            self::BAG,
            self::RING,
            self::EARRING,
            self::NECKLACE,
            self::QUIVER,
            self::ARTIFACT,
        ], true);
    }

    /**
     * Типы брони — по одному на слот доспеха. Кузница (заточка, камни, разбор) работает с ними одинаково.
     *
     * @return list<self>
     */
    public static function armorTypes(): array
    {
        return [
            self::HELMET,
            self::SHOULDER,
            self::FOREARM,
            self::ARMOR,
            self::LEGGING,
            self::CHAIN_ARMOR,
            self::CLOAK,
            self::SHOES,
            self::GLOVES,
        ];
    }

    public function isArmor(): bool
    {
        return in_array($this, self::armorTypes(), true);
    }

    /** Тип брони для слота доспеха (слот и тип совпадают по значению). */
    public static function forArmorSlot(ShareItemSlot $slot): self
    {
        $type = self::from($slot->value);
        if (! $type->isArmor()) {
            throw new \InvalidArgumentException("Слот {$slot->value} не является слотом брони.");
        }

        return $type;
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
            self::HELMET => __('Шлем'),
            self::SHOULDER => __('Наплечники'),
            self::FOREARM => __('Наручи'),
            self::LEGGING => __('Поножи'),
            self::CHAIN_ARMOR => __('Кольчуга'),
            self::CLOAK => __('Плащ'),
            self::SHOES => __('Обувь'),
            self::GLOVES => __('Перчатки'),
            self::BELT => __('Пояс'),
            self::BAG => __('Сумка'),
            self::RING => __('Кольцо'),
            self::EARRING => __('Серьга'),
            self::NECKLACE => __('Ожерелье'),
            self::QUIVER => __('Колчан'),
            self::ARROW => __('Стрелы'),
            self::KEY => __('Ключ'),
            self::LOCKPICK => __('Отмычка'),
            self::TRADE_GOODS => __('Торговый товар'),
            self::POTION => __('Эликсир'),
            self::SCROLL => __('Свиток'),
            self::TREASURE_MAP => __('treasure.map_type'),
            self::TREASURE_MAP_FRAGMENT => __('treasure.fragment_type'),
            self::QUEST => __('Квест'),
            self::ARTIFACT => __('Артефакт'),
            self::RECIPE => __('Рецепт'),
            self::BOOK => __('Книга заклинаний'),
            self::BOSS_MANUAL => __('secret_knowledge.boss_manual_type'),
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
                ...self::armorTypes(),
                self::BELT,
                self::BAG,
                self::RING,
                self::EARRING,
                self::NECKLACE,
                self::QUIVER,
                self::ARROW,
                self::RESOURCE,
                self::FISH,
                self::PRECIOUS_GEM,
                self::PLANT,
                self::WOOD,
                self::RECIPE,
                self::BOOK,
                self::BOSS_MANUAL,
                self::CHEST,
                self::LOCKPICK,
                self::TRADE_GOODS,
                self::SCROLL,
                self::TREASURE_MAP,
                self::TREASURE_MAP_FRAGMENT,
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
