<?php

declare(strict_types=1);

namespace App\Modules\Premium\Application\Services;

use App\Modules\Premium\Domain\Enums\PremiumBonus;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumLevel;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumPeriod;
use Illuminate\Support\Facades\Schema;

final class PremiumBonusService
{
    private array $periods = [];

    private ?bool $ready = null;

    private ?array $levels = null;

    public function ready(): bool
    {
        return $this->ready ??= Schema::hasTable('premium_levels') && Schema::hasTable('premium_periods');
    }

    public function period(int $userId): ?PremiumPeriod
    {
        if ($userId <= 0) {
            return null;
        }
        if (! $this->ready()) {
            return null;
        }
        if (! array_key_exists($userId, $this->periods)) {
            $this->periods[$userId] = PremiumPeriod::query()->where('user_id', $userId)
                ->where('expires_at', '>', now())->orderByDesc('id')->first();
        }
        $period = $this->periods[$userId];

        return $period && $period->expires_at->isFuture() ? $period : null;
    }

    public function level(int $userId): ?PremiumLevel
    {
        $period = $this->period($userId);
        if (! $period) {
            return null;
        }
        $this->levels ??= PremiumLevel::query()->whereNotNull('required_diamond_cents')->orderByDesc('level')->get()->all();
        foreach ($this->levels as $level) {
            if ($period->diamond_cents >= $level->required_diamond_cents) {
                return $level;
            }
        }

        return null;
    }

    public function value(int $userId, PremiumBonus $bonus): float
    {
        $level = $this->level($userId);
        if (! $level || ! $level->bonusEnabled($bonus)) {
            return 0;
        }

        return max(0, min($bonus->max(), (float) ($level->bonuses[$bonus->value] ?? 0)));
    }

    public function forget(int $userId): void
    {
        unset($this->periods[$userId]);
        $this->levels = null;
    }

    public function canAutoLoot(int $userId): bool
    {
        return (int) ($this->level($userId)?->level ?? 0) >= 3
            && $this->value($userId, PremiumBonus::AUTO_LOOT) > 0;
    }
}
