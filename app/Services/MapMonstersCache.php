<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Кэш публичного каталога монстров карты (HomeController::publicMapMonsters).
 *
 * Данные почти статичны, поэтому TTL длинный (сутки), а актуальность
 * обеспечивается принудительной инвалидацией при изменении монстров,
 * их привязок к локациям и переносе локаций между картами.
 *
 * Инвалидация построена на смене версии в ключе: flush() не требует
 * запросов к БД (не зависит от таблицы maps) и работает в любом окружении,
 * включая тесты на sqlite без миграций. Записи старых версий вытесняются TTL.
 */
class MapMonstersCache
{
    /** @var int Время жизни записи кэша в минутах */
    public const TTL_MINUTES = 1440;

    private const VERSION_KEY = 'map:monsters:version';

    public static function key(int|string $mapId): string
    {
        return 'map:monsters:v'.self::version().':'.$mapId;
    }

    /**
     * Сбрасывает кэш каталога для всех карт одной записью в кэш.
     */
    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}
