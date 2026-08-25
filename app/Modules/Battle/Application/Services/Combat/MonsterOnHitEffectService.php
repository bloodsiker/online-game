<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Domain\Enums\EffectDamageScalingType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

readonly class MonsterOnHitEffectService
{
    public function __construct(
        private BattleEffectService $effectService,
        private RandomizerInterface $random,
    ) {}

    /**
     * Applies only explicitly assigned signature effects. A miss, a fully
     * blocked hit or a fatal hit cannot leave a new lingering effect.
     */
    public function apply(
        Player $player,
        Monster $monster,
        FightHitDTO $hit,
        AttackResultDTO $result,
        int $targetMaxHp = 0,
    ): void {
        if ($hit->isDodge() || $hit->getDamage() <= 0 || (int) $player->hp_now <= 0) {
            return;
        }

        $monster->loadMissing('effects');

        foreach ($monster->effects as $effect) {
            if (! (bool) $effect->pivot?->trigger_on_hit || ! $this->canBeAppliedAfterHit($effect)) {
                continue;
            }

            $chance = (float) ($effect->pivot?->chance ?? $effect->chance ?? 0);
            if (! $this->random->chance($chance)) {
                continue;
            }

            $durationSeconds = max(1, (int) ($effect->pivot?->duration_seconds ?? 1));
            $damage = $this->periodicDamage(
                $effect,
                $hit,
                $durationSeconds,
                max(1, $targetMaxHp ?: (int) ($player->hp_max ?: $player->hp_now)),
            );

            $this->effectService->applyEffectToPlayer(
                $effect,
                $player,
                null,
                $result,
                $durationSeconds,
                tickValueOverride: $damage['tick_value'],
            );

            $this->logAppliedEffect($monster, $effect, $result);
        }
    }

    private function canBeAppliedAfterHit(Effect $effect): bool
    {
        if ($effect->type !== 'debuff') {
            return false;
        }

        $activeType = $effect->resolvedActiveType();

        if ($activeType === null) {
            return ! empty($effect->stat_modifiers);
        }

        // Контроль живёт по expires_at и может безопасно накладываться после удара.
        // Регенерация не является отрицательным эффектом цели.
        return $activeType !== ActiveEffectType::REGEN;
    }

    /** @return array{tick_value: float|null, total_damage: int|null} */
    private function periodicDamage(
        Effect $effect,
        FightHitDTO $hit,
        int $durationSeconds,
        int $targetMaxHp,
    ): array {
        if (! $effect->resolvedActiveType()?->isDoT()) {
            return ['tick_value' => null, 'total_damage' => null];
        }

        $powerPercent = $effect->pivot?->power_percent;
        $scalingType = $effect->resolvedDamageScalingType();
        $tickSeconds = max(1, (int) $effect->tick_interval);
        $totalTicks = max(1, intdiv($durationSeconds, $tickSeconds));

        if ($scalingType === EffectDamageScalingType::FIXED || $powerPercent === null) {
            $tickValue = max(0, (float) $effect->value_per_tick);

            return [
                'tick_value' => $tickValue,
                'total_damage' => (int) round($tickValue * $totalTicks),
            ];
        }

        if ($scalingType === EffectDamageScalingType::TARGET_MAX_HP) {
            $rawTotalDamage = $targetMaxHp * max(0, (float) $powerPercent) / 100;
            $totalDamage = $rawTotalDamage > 0 ? max(1, (int) round($rawTotalDamage)) : 0;

            return [
                'tick_value' => $totalDamage / $totalTicks,
                'total_damage' => $totalDamage,
            ];
        }

        $rawTickDamage = $hit->getDamage() * max(0, (float) $powerPercent) / 100;
        $tickValue = $rawTickDamage > 0 ? max(1, (int) round($rawTickDamage)) : 0;

        return [
            'tick_value' => (float) $tickValue,
            'total_damage' => $tickValue * $totalTicks,
        ];
    }

    private function logAppliedEffect(Monster $monster, Effect $effect, AttackResultDTO $result): void
    {
        $activeType = $effect->resolvedActiveType();
        $emoji = $activeType?->emoji() ?? '⚠️';

        $result->log(sprintf(
            '<p class="color-debuff">%s После удара %s на вас наложен эффект <b>%s</b></p>',
            $emoji,
            $monster->name,
            $effect->name,
        ));
    }
}
