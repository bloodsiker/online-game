<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\Services;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Усталость игрока. Хранится в players.fatigue, спадает лениво (при обращении),
 * при максимуме включает сон (players.sleep_until).
 *
 * Сервис меняет атрибуты модели, но не сохраняет её: вызывающий код в бою и так
 * сохраняет $player. Источник усталости задаёт стоимость действия в config/fatigue.php.
 */
final class FatigueService
{
    public function max(): int
    {
        return max(1, (int) config('fatigue.max', 100));
    }

    public function warnAt(): int
    {
        return (int) config('fatigue.warn_at', 70);
    }

    public function cost(string $source): int
    {
        return max(0, (int) config('fatigue.costs.'.$source, 1));
    }

    /** Текущая усталость с учётом спада и пробуждения. */
    public function current(Player $player, ?CarbonInterface $now = null): int
    {
        $this->sync($player, $now ?? Carbon::now());

        return (int) $player->fatigue;
    }

    public function isAsleep(Player $player, ?CarbonInterface $now = null): bool
    {
        $now ??= Carbon::now();
        $this->sync($player, $now);

        return $player->sleep_until !== null && $player->sleep_until->isAfter($now);
    }

    public function sleepSecondsLeft(Player $player, ?CarbonInterface $now = null): int
    {
        $now ??= Carbon::now();

        return $this->isAsleep($player, $now) ? max(1, (int) $now->diffInSeconds($player->sleep_until)) : 0;
    }

    /** Сообщение «вы спите…» или null, если игрок бодрствует. */
    public function sleepMessage(Player $player): ?string
    {
        $seconds = $this->sleepSecondsLeft($player);

        return $seconds > 0
            ? __('Вы спите от переутомления. Проснётесь через :minutes мин.', ['minutes' => (int) ceil($seconds / 60)])
            : null;
    }

    /** Блокирует действие, если игрок спит. */
    public function assertAwake(Player $player): void
    {
        $message = $this->sleepMessage($player);
        if ($message !== null) {
            abort(409, $message);
        }
    }

    /**
     * Прибавляет усталость за $units действий источника $source.
     * Возвращает true, если от этого игрок уснул.
     */
    public function add(Player $player, string $source, int $units = 1, ?CarbonInterface $now = null): bool
    {
        $now ??= Carbon::now();
        $this->sync($player, $now);

        $amount = $this->cost($source) * max(0, $units);
        if ($amount === 0) {
            return false;
        }

        $player->fatigue = min($this->max(), (int) $player->fatigue + $amount);
        $player->fatigue_updated_at ??= $now;

        if ($player->fatigue >= $this->max() && ($player->sleep_until === null || ! $player->sleep_until->isAfter($now))) {
            $player->sleep_until = $now->copy()->addMinutes(max(1, (int) config('fatigue.sleep_minutes', 15)));

            return true;
        }

        return false;
    }

    /** Применяет спад усталости и пробуждение. */
    private function sync(Player $player, CarbonInterface $now): void
    {
        if ($player->sleep_until !== null && ! $player->sleep_until->isAfter($now)) {
            $player->sleep_until = null;
            $player->fatigue = min((int) $player->fatigue, max(0, (int) config('fatigue.wake_fatigue', 50)));
            $player->fatigue_updated_at = $player->fatigue > 0 ? $now : null;

            return;
        }

        $fatigue = (int) $player->fatigue;
        if ($fatigue <= 0) {
            $player->fatigue_updated_at = null;

            return;
        }

        // Во сне усталость не спадает: игрок спит ровно положенное время.
        if ($player->sleep_until !== null || $player->fatigue_updated_at === null) {
            $player->fatigue_updated_at ??= $now;

            return;
        }

        $rate = (float) config('fatigue.regen_per_minute', 3);
        if ($rate <= 0) {
            return;
        }

        $elapsed = max(0, $player->fatigue_updated_at->diffInSeconds($now, false));
        $points = (int) floor($elapsed * $rate / 60);
        if ($points <= 0) {
            return;
        }

        $player->fatigue = max(0, $fatigue - $points);
        // Сдвигаем базу только на «использованное» время, чтобы дробные остатки не терялись.
        $player->fatigue_updated_at = $player->fatigue > 0
            ? $player->fatigue_updated_at->copy()->addSeconds((int) ceil($points * 60 / $rate))
            : null;
    }
}
