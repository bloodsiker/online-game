<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

/**
 * Словарь боевых пассивок, общий для двух НЕЗАВИСИМЫХ потребителей с
 * разной семантикой значения:
 *   - руны/встроенные пассивки предметов (share_items.innate_passive_type,
 *     item_runes.passive_skill, см. PlayerRunePassiveService) — $value это
 *     ШАНС срабатывания при фиксированной величине эффекта;
 *   - встроенные пассивки монстров (monsters.passive_type/passive_value,
 *     см. MonsterAttackService::applyPassive()) — срабатывает каждый удар
 *     БЕЗ броска шанса, а $value задаёт саму величину эффекта.
 *
 * Раньше назывался RunePassiveType и жил в Structure/Blacksmith — название
 * и расположение вводили в заблуждение, когда тот же enum стали применять
 * к монстрам напрямую, без всякой руны. Сам словарь (7 типов) переиспользуется
 * сознательно — то же самое понятие «Вампиризм»/«Оглушение»/... не нужно
 * дублировать под каждого потребителя; расходится только интерпретация
 * $value, поэтому у description() (руна) и monsterDescription() (монстр)
 * разные тексты для одного и того же case.
 */
enum CombatPassiveType: string
{
    case VAMPIRISM = 'vampirism';      // хил % от нанесённого урона
    case CHAIN_ATTACK = 'chain_attack';   // удар по второй цели за % урона
    case STUN = 'stun';           // % шанс оглушить на 10 секунд
    case DOUBLE_STRIKE = 'double_strike';  // % шанс ударить дважды
    case REFLECT = 'reflect';        // отражение % входящего урона
    case RAGE = 'rage';           // +% урон когда HP < 30%
    case SHIELD = 'shield';         // % шанс полностью заблокировать атаку

    public function label(): string
    {
        return match ($this) {
            self::VAMPIRISM => 'Вампиризм',
            self::CHAIN_ATTACK => 'Цепная атака',
            self::STUN => 'Оглушение',
            self::DOUBLE_STRIKE => 'Двойной удар',
            self::REFLECT => 'Отражение',
            self::RAGE => 'Ярость',
            self::SHIELD => 'Щит',
        };
    }

    /** Описание ДЛЯ РУНЫ/встроенной пассивки предмета — $value это шанс срабатывания. */
    public function description(int|float $value): string
    {
        return match ($this) {
            self::VAMPIRISM => "При атаке {$value}% шанс восстановить 10% нанесённого урона",
            self::CHAIN_ATTACK => "При атаке {$value}% шанс поразить второго врага за 50% урона",
            self::STUN => "При атаке {$value}% шанс оглушить цель сокрушительным ударом на 10 секунд",
            self::DOUBLE_STRIKE => "При атаке {$value}% шанс ударить дважды",
            self::REFLECT => "Отражает {$value}% входящего урона обратно атакующему",
            self::RAGE => "При HP < 30% увеличивает урон на {$value}%",
            self::SHIELD => "При получении урона {$value}% шанс полностью заблокировать удар",
        };
    }

    /**
     * Описание ДЛЯ МОНСТРА (monsters.passive_type/passive_value, см.
     * MonsterAttackService::applyPassive()) — НЕ то же самое, что
     * description(): у рун $value — это шанс срабатывания при фиксированной
     * величине эффекта (например, вампиризм руны — 10% хила с шансом
     * $value%). У монстра наоборот: срабатывает каждый удар без броска
     * шанса, а $value задаёт саму величину эффекта. Переиспользовать
     * description() для монстра означало бы показать неверный текст вроде
     * «15% шанс восстановить 10%» вместо настоящего поведения «каждый
     * удар восстанавливает 15%».
     */
    public function monsterDescription(int|float $value): string
    {
        return match ($this) {
            self::VAMPIRISM => "При каждом ударе монстр восстанавливает себе {$value}% от нанесённого урона как HP",
            self::CHAIN_ATTACK => "При каждом ударе монстр дополнительно поражает второго врага за {$value}% урона",
            self::STUN => "Каждый удар монстра оглушает цель на {$value} сек.",
            self::DOUBLE_STRIKE => "Каждый удар монстра наносится дважды с силой {$value}% от второго удара",
            self::REFLECT => "Монстр отражает {$value}% полученного урона обратно атакующему",
            self::RAGE => "При HP < 30% урон монстра увеличивается на {$value}%",
            self::SHIELD => "Монстр блокирует {$value}% входящих ударов полностью",
        };
    }

    /** Диапазон значений для пассивки РУНЫ при крафте [min, max] — для монстра не применяется, там своя шкала (0-255, см. MonsterController). */
    public function valueRange(): array
    {
        return match ($this) {
            self::VAMPIRISM => [8,  15],
            self::CHAIN_ATTACK => [15, 25],
            self::STUN => [8,  15],
            self::DOUBLE_STRIKE => [12, 20],
            self::REFLECT => [5,  10],
            self::RAGE => [15, 25],
            self::SHIELD => [8,  15],
        };
    }

    /**
     * Путь к иконке пассивки (под public/), один файл на тип — enum
     * фиксирован, поэтому не нужна отдельная колонка/таблица под иконки.
     * Используется везде, где показывается пассивка игрока/монстра (руна
     * предмета, монстр на /info/m/{id}).
     */
    public function iconPath(): string
    {
        return 'img/passives/'.$this->value.'.png';
    }

    /** Абсолютный URL иконки — настоящий файл, если он уже загружен, иначе общая заглушка. */
    public function iconUrl(): string
    {
        $relative = $this->iconPath();

        return file_exists(public_path($relative))
            ? asset($relative)
            : asset('main/images/effects-unavailable.png');
    }
}
