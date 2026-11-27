<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Item\Domain\Exceptions\ItemUseBlockedException;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Premium\Application\Services\PremiumBonusService;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumLevel;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumPeriod;
use Carbon\CarbonInterface;

/**
 * «Сфера премиума»: value — уровень премиума (1–5), duration_seconds — на сколько он даётся.
 *
 * Нет активного премиума — создаётся период нужного уровня. Есть премиум того же или более
 * высокого уровня — время сферы прибавляется к его сроку. Активный премиум ниже уровня сферы —
 * использовать её нельзя (предмет не расходуется, игрок видит причину).
 */
final class GrantPremiumStrategy implements ItemEffectStrategyInterface
{
    public function __construct(private readonly PremiumBonusService $bonuses) {}

    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        $this->grant($player, $effect);
    }

    /** @return array{level: int, expires_at: CarbonInterface} */
    public function grant(Player $player, ItemEffectValue $effect): array
    {
        $level = $effect->value;
        $seconds = (int) $effect->durationSeconds;

        if (! $effect->isFlat() || $level < 1 || $level > 5 || $seconds <= 0) {
            throw new ItemUseBlockedException(
                message: __('Неверно настроена сфера премиума. Обратитесь к администрации.'),
                errorCode: 'invalid_premium_sphere',
            );
        }

        $threshold = PremiumLevel::query()->whereKey($level)->value('required_diamond_cents');
        if ($threshold === null) {
            throw new ItemUseBlockedException(
                message: __('Премиум пока не настроен. Попробуйте позже.'),
                errorCode: 'premium_not_configured',
            );
        }

        $userId = (int) $player->user_id;
        $now = now();
        $period = PremiumPeriod::query()
            ->where('user_id', $userId)
            ->where('expires_at', '>', $now)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($period !== null) {
            $this->bonuses->forget($userId);
            $currentLevel = (int) ($this->bonuses->level($userId)?->level ?? 0);

            if ($currentLevel < $level) {
                throw new ItemUseBlockedException(
                    message: __('Эта сфера даёт премиум :level уровня, а у вас активен премиум :current уровня. Сферу можно использовать, когда ваш премиум не ниже её уровня.', [
                        'level' => $level,
                        'current' => $currentLevel,
                    ]),
                    errorCode: 'premium_level_too_low',
                );
            }

            $period->expires_at = $period->expires_at->addSeconds($seconds);
            $period->save();
        } else {
            $period = PremiumPeriod::query()->create([
                'user_id' => $userId,
                'diamond_cents' => (int) $threshold,
                'started_at' => $now,
                'expires_at' => $now->copy()->addSeconds($seconds),
            ]);
        }

        $this->bonuses->forget($userId);

        return ['level' => $level, 'expires_at' => $period->expires_at];
    }
}
