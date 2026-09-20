<?php

declare(strict_types=1);

namespace App\Modules\Scheduler\Domain\Enums;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Console\Scheduling\Event;

enum ScheduledTaskFrequency: string
{
    case EVERY_FIVE_SECONDS = 'every_five_seconds';
    case EVERY_TEN_SECONDS = 'every_ten_seconds';
    case EVERY_THIRTY_SECONDS = 'every_thirty_seconds';
    case EVERY_MINUTE = 'every_minute';
    case EVERY_FIVE_MINUTES = 'every_five_minutes';
    case EVERY_FIFTEEN_MINUTES = 'every_fifteen_minutes';
    case HOURLY = 'hourly';
    case DAILY = 'daily';

    public function label(): string
    {
        return match ($this) {
            self::EVERY_FIVE_SECONDS => 'Каждые 5 секунд',
            self::EVERY_TEN_SECONDS => 'Каждые 10 секунд',
            self::EVERY_THIRTY_SECONDS => 'Каждые 30 секунд',
            self::EVERY_MINUTE => 'Каждую минуту',
            self::EVERY_FIVE_MINUTES => 'Каждые 5 минут',
            self::EVERY_FIFTEEN_MINUTES => 'Каждые 15 минут',
            self::HOURLY => 'Каждый час',
            self::DAILY => 'Раз в сутки',
        };
    }

    public function apply(Event $event): Event
    {
        return match ($this) {
            self::EVERY_FIVE_SECONDS => $event->everyFiveSeconds(),
            self::EVERY_TEN_SECONDS => $event->everyTenSeconds(),
            self::EVERY_THIRTY_SECONDS => $event->everyThirtySeconds(),
            self::EVERY_MINUTE => $event->everyMinute(),
            self::EVERY_FIVE_MINUTES => $event->everyFiveMinutes(),
            self::EVERY_FIFTEEN_MINUTES => $event->everyFifteenMinutes(),
            self::HOURLY => $event->hourly(),
            self::DAILY => $event->daily(),
        };
    }

    public function nextRunAt(CarbonInterface $now): CarbonImmutable
    {
        $now = CarbonImmutable::instance($now);

        return match ($this) {
            self::EVERY_FIVE_SECONDS => $this->nextSeconds($now, 5),
            self::EVERY_TEN_SECONDS => $this->nextSeconds($now, 10),
            self::EVERY_THIRTY_SECONDS => $this->nextSeconds($now, 30),
            self::EVERY_MINUTE => $now->addMinute()->startOfMinute(),
            self::EVERY_FIVE_MINUTES => $this->nextMinutes($now, 5),
            self::EVERY_FIFTEEN_MINUTES => $this->nextMinutes($now, 15),
            self::HOURLY => $now->addHour()->startOfHour(),
            self::DAILY => $now->addDay()->startOfDay(),
        };
    }

    private function nextSeconds(CarbonImmutable $now, int $step): CarbonImmutable
    {
        $seconds = $step - ($now->second % $step);

        return $now->addSeconds($seconds)->startOfSecond();
    }

    private function nextMinutes(CarbonImmutable $now, int $step): CarbonImmutable
    {
        $minutes = $step - ($now->minute % $step);

        return $now->addMinutes($minutes)->startOfMinute();
    }
}
