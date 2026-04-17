<?php

namespace App\Enums;

enum ShareItemType: string
{
    case RESOURCE = 'resource';
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
    case CHEST = 'chest';
    case SCROLL = 'scroll';
    case GIFT = 'gift';
    case GEM = 'gem';
    case SOCKET_KIT = 'socket_kit';
    case RUNE = 'rune';
    case RUNE_KEY = 'rune_key';

    public function label(): string
    {
        return match ($this) {
            self::RESOURCE => 'Ресурсы',
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
            self::CHEST => 'Сундук',
            self::EAT => 'Еда',
            self::GIFT => 'Подарок',
            self::GEM => 'Камень',
            self::SOCKET_KIT => 'Набор для сокета',
            self::RUNE => 'Руна',
            self::RUNE_KEY => 'Рунный ключ',
        };
    }

    public static function group(string $group): array
    {
        return match ($group) {
            'main' => [
                self::POTION,
                self::WEAPON,
                self::SHIELD,
                self::ARMOR,
                self::BELT,
                self::BAG,
                self::RESOURCE,
                self::RECIPE,
                self::SCROLL,
            ],
            'key' => [self::KEY],
            'quest' => [self::QUEST],
            'artifact' => [self::ARTIFACT],
            'gift' => [self::GIFT],
            default => self::group('main'),
        };
    }

    public static function values(array $cases): array
    {
        return array_map(fn (self $type) => $type->value, $cases);
    }
}
