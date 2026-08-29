<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

enum ShareItemType: string
{
    case RESOURCE = 'resource';
    case TOOL = 'tool';
    case WEAPON = 'weapon';
    case SHIELD = 'shield';
    case ARMOR = 'armor';
    case BELT = 'belt';
    case BAG = 'bag';
    case KEY = 'key';
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
        ], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::RESOURCE => 'Ресурсы',
            self::TOOL => 'Инструмент',
            self::WEAPON => 'Оружие',
            self::SHIELD => 'Щит',
            self::ARMOR => 'Броня',
            self::BELT => 'Пояс',
            self::BAG => 'Сумка',
            self::KEY => 'Ключ',
            self::POTION => 'Эликсир',
            self::SCROLL => 'Свиток',
            self::QUEST => 'Квест',
            self::ARTIFACT => 'Артефакт',
            self::RECIPE => 'Рецепт',
            self::BOOK => 'Книга заклинаний',
            self::CHEST => 'Сундук',
            self::EAT => 'Еда',
            self::GIFT => 'Подарок',
            self::GEM => 'Камень',
            self::MOUNT => 'Оправа',
            self::RUNE => 'Руна',
            self::RUNE_KEY => 'Рунный ключ',
            self::MISC => 'Разное',
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
                self::RECIPE,
                self::BOOK,
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
