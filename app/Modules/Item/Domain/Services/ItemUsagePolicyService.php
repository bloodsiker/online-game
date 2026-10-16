<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Services;

use App\Modules\Item\Domain\Exceptions\ItemUseBlockedException;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerItemUseLimitState;
use App\Modules\Share\Domain\Enums\ItemBuffReapplyPolicy;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Carbon\CarbonInterface;

class ItemUsagePolicyService
{
    /**
     * Проверяет все правила и сразу резервирует одно использование.
     * Метод должен вызываться внутри общей транзакции использования предмета:
     * если применение эффекта или списание завершится ошибкой, счётчик откатится.
     */
    public function reserveUse(Player $player, ShareItem $item): void
    {
        $this->ensureBuffCanBeReapplied($player, $item);

        $limit = $item->useLimit;
        if ($limit === null) {
            return;
        }

        $now = now();
        $state = PlayerItemUseLimitState::query()
            ->where('player_id', $player->id)
            ->where('share_item_use_limit_id', $limit->id)
            ->lockForUpdate()
            ->first();

        if ($state === null) {
            PlayerItemUseLimitState::query()->create([
                'player_id' => $player->id,
                'share_item_use_limit_id' => $limit->id,
                'period_started_at' => $now,
                'uses_count' => 1,
            ]);

            return;
        }

        $availableAt = $state->period_started_at->copy()->addSeconds($limit->period_seconds);
        if ($availableAt->lte($now)) {
            $state->update([
                'period_started_at' => $now,
                'uses_count' => 1,
            ]);

            return;
        }

        if ($state->uses_count >= $limit->max_uses) {
            throw new ItemUseBlockedException(
                sprintf(
                    __('Предмет можно использовать %d %s за %s. Следующее использование будет доступно через %s.'),
                    $limit->max_uses,
                    $this->usesWord($limit->max_uses),
                    $this->formatPeriod($limit->period_seconds),
                    $this->remainingTime($availableAt),
                ),
                'ITEM_USE_LIMIT_REACHED',
                $availableAt,
            );
        }

        $state->increment('uses_count');
    }

    private function ensureBuffCanBeReapplied(Player $player, ShareItem $item): void
    {
        $blockedBuffs = $item->buffs->filter(
            static fn ($buff): bool => $buff->reapply_policy === ItemBuffReapplyPolicy::BLOCK,
        );
        if ($blockedBuffs->isEmpty()) {
            return;
        }

        $activeEffect = PlayerActiveEffect::query()
            ->with('effect')
            ->where('player_id', $player->id)
            ->whereIn('effect_id', $blockedBuffs->pluck('effect_id'))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('expires_at')
            ->first();

        if ($activeEffect === null) {
            return;
        }

        $message = sprintf(__('Эффект «%s» уже действует.'), $activeEffect->effect?->name ?? __('предмета'));
        if ($activeEffect->expires_at !== null) {
            $message .= sprintf(__(' Повторное использование будет доступно через %s.'), $this->remainingTime($activeEffect->expires_at));
        }

        throw new ItemUseBlockedException(
            $message,
            'ITEM_EFFECT_ALREADY_ACTIVE',
            $activeEffect->expires_at,
        );
    }

    private function remainingTime(CarbonInterface $availableAt): string
    {
        return $availableAt->locale(app()->getLocale())->diffForHumans(now(), true, false, 2);
    }

    private function formatPeriod(int $seconds): string
    {
        return match (true) {
            $seconds % 86400 === 0 => $this->formatAmount((int) ($seconds / 86400), __('день'), __('дня'), __('дней')),
            $seconds % 3600 === 0 => $this->formatAmount((int) ($seconds / 3600), __('час'), __('часа'), __('часов')),
            $seconds % 60 === 0 => $this->formatAmount((int) ($seconds / 60), __('минуту'), __('минуты'), __('минут')),
            default => $this->formatAmount($seconds, __('секунду'), __('секунды'), __('секунд')),
        };
    }

    private function formatAmount(int $value, string $one, string $few, string $many): string
    {
        $mod100 = $value % 100;
        $mod10 = $value % 10;
        $word = $mod100 >= 11 && $mod100 <= 14
            ? $many
            : match ($mod10) {
                1 => $one,
                2, 3, 4 => $few,
                default => $many,
            };

        return $value.' '.$word;
    }

    private function usesWord(int $count): string
    {
        $mod100 = $count % 100;
        $mod10 = $count % 10;

        if ($mod100 >= 11 && $mod100 <= 14) {
            return __('раз');
        }

        return match ($mod10) {
            1 => __('раз'),
            2, 3, 4 => __('раза'),
            default => __('раз'),
        };
    }
}
