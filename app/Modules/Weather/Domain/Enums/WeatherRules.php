<?php

declare(strict_types=1);

namespace App\Modules\Weather\Domain\Enums;

/** Числовые параметры погоды (баланс правится здесь). */
final class WeatherRules
{
    /** Шанс «Простуды» при входе на локацию в грозу, % */
    public const COLD_CHANCE = 15;

    public const COLD_STAT_PERCENT = -10;

    public const RAINBOW_STAT_PERCENT = 5;

    /** Длительность «Простуды» и «Радуги», мин */
    public const EFFECT_MINUTES = 15;

    /** Туман: на сколько процентов противник чаще уворачивается от ударов игрока */
    public const FOG_PERCENT = 15;

    /** Шанс радуги после окончания грозы, % */
    public const RAINBOW_AFTER_STORM_CHANCE = 50;

    public const WEATHER_MIN_MINUTES = 20;

    public const WEATHER_MAX_MINUTES = 40;

    public const RAINBOW_MINUTES = 15;

    public const CLEAR_MIN_MINUTES = 30;

    public const CLEAR_MAX_MINUTES = 60;

    public const COLD_EFFECT_SLUG = 'weather_cold';

    public const RAINBOW_EFFECT_SLUG = 'weather_rainbow';

    public const FOG_EFFECT_SLUG = 'weather_fog';

    /** Дебафф «Туман»: снижение характеристик Крит и Уворот, % */
    public const FOG_STAT_PERCENT = -25;

    /** Явления, механика которых уже реализована (только они выпадают при смене погоды). */
    public const ACTIVE_TYPES = ['storm', 'fog', 'blizzard', 'tailwind', 'hurricane', 'heat'];

    /** Зной: множитель скорости регенерации HP и маны */
    public const HEAT_REGEN_SPEED = 0.5;

    /** Метель: длительность перехода между локациями, мс (обычно 1200) */
    public const BLIZZARD_MOVE_MS = 2000;

    /** Попутный ветер: длительность перехода, мс */
    public const TAILWIND_MOVE_MS = 500;

    /** Ураган: шанс, что после перехода игрока отбросит на соседнюю локацию, % */
    public const HURRICANE_CHANCE = 5;
}
