<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Effect\Application\DTOs\PlayerEffectNotificationDTO;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterActiveEffect;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Domain\Services\PlayerTimedEffectService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Carbon\CarbonInterface;

class BattleEffectService
{
    /** Боссы не иммунны к дебаффам, но держат их вдвое короче — см. спеку */
    private const BOSS_DEBUFF_DURATION_MULTIPLIER = 0.5;

    public function __construct(
        private readonly PlayerStatService $statService,
        private readonly PlayerTimedEffectService $timedEffectService,
    ) {}

    /**
     * Apply an Effect model (from player magic skill) to the monster.
     */
    public function applyEffectToMonster(
        Effect $effect,
        MonsterOnLocation $locMonster,
        Battle $battle,
        AttackResultDTO $result,
        int $durationSeconds,
        int|float|null $tickValueOverride = null,
    ): void {
        $type = $effect->resolvedActiveType();

        // Дебафф без соответствующего ActiveEffectType (например, чистый
        // "минус к стату") всё равно должен пройти — иначе строка
        // MonsterActiveEffect никогда не создаётся и MonsterCombatant её не
        // увидит. Игнорируем только по-настоящему неизвестные НЕ-дебаффы.
        if ($type === null && $effect->type !== 'debuff') {
            return; // Неизвестный тип эффекта, не дебафф — игнорируем
        }

        $effectiveDuration = $type?->isControl()
            ? max(1, $durationSeconds)
            : max(0, $durationSeconds);

        if ($effect->type === 'debuff' && $locMonster->monster->is_boss) {
            $effectiveDuration = max(1, (int) round($effectiveDuration * self::BOSS_DEBUFF_DURATION_MULTIPLIER));
        }

        // Эффект принадлежит конкретному спавну моба, не бою: если игрок
        // выйдет из боя, ожог/дебафф продолжит действовать до expires_at.
        // Старые battle-bound строки намеренно не подхватываем: они относятся
        // к прежней, пошаговой модели и будут очищены как legacy-данные.
        $existing = MonsterActiveEffect::where('location_monster_id', $locMonster->id)
            ->whereNull('battle_id')
            ->when(
                $type !== null,
                fn ($query) => $query->where('type', $type),
                fn ($query) => $query->where('effect_id', $effect->id),
            )
            ->first();

        if ($existing) {
            $existing->stacks = $effectiveDuration;
            $existing->expires_at = $effectiveDuration > 0 ? now()->addSeconds($effectiveDuration) : null;
            $existing->last_tick_at = now();
            if ($tickValueOverride !== null) {
                $existing->current_value = $tickValueOverride;
            }
            $existing->save();

            return;
        }

        MonsterActiveEffect::create([
            'location_monster_id' => $locMonster->id,
            'effect_id' => $effect->id,
            'battle_id' => null,
            'type' => $type,
            'applied_at' => now(),
            'last_tick_at' => now(),
            'expires_at' => $effectiveDuration > 0 ? now()->addSeconds($effectiveDuration) : null,
            'stacks' => $effectiveDuration,
            'current_value' => $tickValueOverride ?? (float) $effect->value_per_tick,
        ]);
    }

    /**
     * Apply an Effect model (from player buff magic skill) to the player himself.
     */
    /**
     * Apply an Effect model to a player.
     *
     * Эффект игрока всегда живёт в реальном времени: наложенный в бою баф,
     * DoT или дебаф продолжает действовать после его завершения.
     */
    public function applyEffectToPlayer(
        Effect $effect,
        Player $player,
        ?Battle $battle,
        AttackResultDTO $result,
        int $durationSeconds,
        int|float|null $tickValueOverride = null,
    ): void {
        $type = $effect->resolvedActiveType();
        // null type = buff with stat_modifiers, no DoT/stun processing needed

        $existing = PlayerActiveEffect::where('player_id', $player->id)
            ->whereNull('battle_id')
            ->when(
                $type !== null,
                fn ($query) => $query->where('type', $type),
                fn ($query) => $query->where('effect_id', $effect->id),
            )
            ->first();

        $effectiveDuration = $type?->isControl()
            ? max(1, $durationSeconds)
            : max(0, $durationSeconds);
        $expiresAt = match (true) {
            $effectiveDuration > 0 => now()->addSeconds($effectiveDuration),
            default => null,
        };

        if ($existing) {
            $existing->effect_id = $effect->id;
            $existing->type = $type;
            $existing->applied_at = now();
            $existing->expires_at = $expiresAt;
            $existing->last_tick_at = now();
            $existing->current_value = $tickValueOverride ?? (float) $effect->value_per_tick;
            $existing->tick_remainder = 0;
            $existing->save();
            $activeEffect = $existing;
        } else {
            $activeEffect = PlayerActiveEffect::create([
                'player_id' => $player->id,
                'effect_id' => $effect->id,
                'battle_id' => null,
                'type' => $type,
                'applied_at' => now(),
                'last_tick_at' => now(),
                'expires_at' => $expiresAt,
                'stacks' => 0,
                'current_value' => $tickValueOverride ?? (float) $effect->value_per_tick,
                'tick_remainder' => 0,
            ]);
        }

        $this->statService->invalidate($player);
        $this->notifyPlayerFrame($result, $activeEffect, $effectiveDuration, $effect);
    }

    /**
     * Apply a custom effect (from boss skill JSON config) to the player.
     */
    public function applyCustomEffectToPlayer(
        ActiveEffectType $type,
        float $value,
        int $durationOrTicks,
        Player $player,
        Battle $battle,
        AttackResultDTO $result
    ): void {
        $isTimedDoT = $type->isDoT();
        $isTimedControl = $type->isControl();
        $isPersistentTimedEffect = $isTimedDoT || $isTimedControl;
        $tickSeconds = max(1, (int) config('game.player_heartbeat_seconds', 10));
        $expiresAt = match (true) {
            $isTimedControl => now()->addSeconds(max(1, $durationOrTicks)),
            $isTimedDoT => now()->addSeconds(max(1, $durationOrTicks) * $tickSeconds),
            default => null,
        };

        $existing = PlayerActiveEffect::where('player_id', $player->id)
            ->where('type', $type)
            ->when(! $isPersistentTimedEffect, fn ($query) => $query->where('battle_id', $battle->id))
            ->first();

        if ($existing) {
            if ($isPersistentTimedEffect) {
                $existing->battle_id = null;
            }
            $existing->stacks = $isTimedControl
                ? 0
                : ($isTimedDoT ? $durationOrTicks : max($existing->stacks, $durationOrTicks));
            $existing->last_tick_at = now();
            $existing->expires_at = $expiresAt;
            $existing->tick_remainder = 0;
            $existing->save();
            $activeEffect = $existing;
        } else {
            $activeEffect = PlayerActiveEffect::create([
                'player_id' => $player->id,
                'battle_id' => $isPersistentTimedEffect ? null : $battle->id,
                'type' => $type,
                'applied_at' => now(),
                'last_tick_at' => now(),
                'expires_at' => $expiresAt,
                'stacks' => $isTimedControl ? 0 : $durationOrTicks,
                'current_value' => $value,
                'tick_remainder' => 0,
            ]);
        }

        $this->statService->invalidate($player);

        $displayDuration = match (true) {
            $isTimedControl => max(1, $durationOrTicks),
            $isTimedDoT => max(1, $durationOrTicks) * $tickSeconds,
            default => 0,
        };
        $this->notifyPlayerFrame($result, $activeEffect, $displayDuration);

        $durationText = $isTimedControl
            ? sprintf('%d сек.', max(1, $durationOrTicks))
            : sprintf('%d тиков', $durationOrTicks);

        $result->log(sprintf(
            '<p class="color-debuff">%s На вас наложен эффект: <b>%s</b> (%s)</p>',
            $type->emoji(),
            $type->label(),
            $durationText,
        ));
    }

    private function notifyPlayerFrame(
        AttackResultDTO $result,
        PlayerActiveEffect $activeEffect,
        int $durationSeconds,
        ?Effect $definition = null,
    ): void {
        if ($durationSeconds <= 0) {
            return;
        }

        $definition ??= $activeEffect->effect;
        $type = $activeEffect->type;
        $isCurse = $type?->isDoT() || $type?->isControl() || $definition?->type === 'debuff';

        $result->notifyPlayerEffect(new PlayerEffectNotificationDTO(
            id: ($definition?->slug ?? 'effect').'_'.$activeEffect->id,
            name: $definition?->name ?? ($type?->label() ?? 'Эффект'),
            duration: $durationSeconds,
            isCurse: $isCurse,
        ));
    }

    /**
     * Apply a custom effect (например, пассивка «Оглушение» от руны) к монстру.
     */
    public function applyCustomEffectToMonster(
        ActiveEffectType $type,
        float $value,
        int $durationSeconds,
        MonsterOnLocation $locMonster,
        Battle $battle,
        AttackResultDTO $result
    ): void {
        $isTimedControl = $type->isControl();
        $expiresAt = $isTimedControl
            ? now()->addSeconds(max(1, $durationSeconds))
            : null;

        $existing = MonsterActiveEffect::where('location_monster_id', $locMonster->id)
            ->where('type', $type)
            ->first();

        if ($existing) {
            if ($isTimedControl) {
                $existing->battle_id = null;
            }
            $existing->stacks = $isTimedControl ? 0 : max($existing->stacks, $durationSeconds);
            $existing->last_tick_at = now();
            $existing->expires_at = $expiresAt;
            $existing->save();

            return;
        }

        MonsterActiveEffect::create([
            'location_monster_id' => $locMonster->id,
            'battle_id' => $isTimedControl ? null : $battle->id,
            'type' => $type,
            'applied_at' => now(),
            'last_tick_at' => now(),
            'expires_at' => $expiresAt,
            'stacks' => $isTimedControl ? 0 : $durationSeconds,
            'current_value' => $value,
        ]);

        $durationText = $isTimedControl
            ? sprintf('%d сек.', max(1, $durationSeconds))
            : sprintf('%d ход(а)', $durationSeconds);

        $result->log(sprintf(
            '<p class="color-debuff">%s %s получает эффект: <b>%s</b> (%s)</p>',
            $type->emoji(),
            $locMonster->monster->name,
            $type->label(),
            $durationText,
        ));
    }

    /**
     * Process active effects on the player this round.
     * Returns true if the player is stunned and should skip their attack.
     */
    public function processPlayerEffects(Player $player, Battle $battle, AttackResultDTO $result): bool
    {
        $now = now();
        $tickResult = $this->timedEffectService->process($player, $now);
        foreach ($tickResult->effects as $processedEffect) {
            $result->log(sprintf(
                '<p class="color-debuff">%s <b>%s</b> наносит вам %d урона!</p>',
                $processedEffect['emoji'],
                $processedEffect['label'],
                $processedEffect['damage'],
            ));
        }

        if ($tickResult->effectsChanged) {
            $this->statService->invalidate($player);
        }

        $effects = PlayerActiveEffect::where('player_id', $player->id)->get();

        $isStunned = false;
        $statsChanged = false;

        foreach ($effects as $effect) {
            $this->upgradeLegacyControlExpiration($effect, $now);

            if ($effect->expires_at !== null && $effect->expires_at->lte($now) && ! $effect->isDoT()) {
                if ($effect->isControl()) {
                    $result->log(sprintf(
                        '<p class="color-info">%s Эффект <b>%s</b> рассеялся.</p>',
                        $effect->type->emoji(),
                        $effect->type->label(),
                    ));
                }
                $statsChanged = $statsChanged || $effect->effect_id !== null;
                $effect->delete();

                continue;
            }

            if ($effect->isControl()) {
                $isStunned = true;
                $remainingSeconds = max(1, (int) $now->diffInSeconds($effect->expires_at, false));
                $result->log(sprintf(
                    '<p class="color-debuff">%s Вы под эффектом <b>%s</b> и не можете атаковать! (осталось %d сек.)</p>',
                    $effect->type->emoji(),
                    $effect->type->label(),
                    $remainingSeconds,
                ));

                continue;
            }

            if ($effect->isDoT()) {
                continue;
            }

            if ($effect->type?->isHoT()) {
                $heal = (int) $effect->current_value;
                $player->hp_now = min($player->hp_max, $player->hp_now + $heal);

                $result->log(sprintf(
                    '<p class="color-buff">%s <b>Регенерация</b> восстанавливает %d HP!</p>',
                    $effect->type->emoji(),
                    $heal
                ));

                $effect->stacks--;
                if ($effect->stacks <= 0) {
                    $statsChanged = $statsChanged || $effect->effect_id !== null;
                    $effect->delete();
                } else {
                    $effect->save();
                }
            }
        }

        if ($statsChanged) {
            $this->statService->invalidate($player);
        }

        return $isStunned;
    }

    /**
     * Process active effects on the monster this round.
     * Returns true if the monster is stunned and should skip its attack.
     */
    public function processMonsterEffects(
        MonsterOnLocation $locMonster,
        Battle $battle,
        AttackResultDTO $result
    ): bool {
        $now = now();
        $effects = MonsterActiveEffect::where('location_monster_id', $locMonster->id)
            // Постоянные магические эффекты моба + старые пошаговые эффекты
            // именно текущего боя (например, оглушение от руны).
            ->where(fn ($query) => $query
                ->whereNull('battle_id')
                ->orWhere('battle_id', $battle->id))
            ->with('effect')
            ->lockForUpdate()
            ->get();

        $isStunned = false;

        foreach ($effects as $effect) {
            $this->upgradeLegacyControlExpiration($effect, $now);

            // DoT сначала обязан забрать все тики, накопившиеся до момента
            // истечения; остальные эффекты можно снять сразу.
            if ($effect->expires_at !== null && $effect->expires_at->lte($now) && ! $effect->isDoT()) {
                $result->log(sprintf(
                    '<p class="color-info">✨ Эффект <b>%s</b> на %s рассеялся.</p>',
                    $effect->effect?->name ?? 'заклинания',
                    $locMonster->monster->name,
                ));
                $effect->delete();

                continue;
            }

            if ($effect->isControl()) {
                $isStunned = true;
                $remainingSeconds = max(1, (int) $now->diffInSeconds($effect->expires_at, false));
                $result->log(sprintf(
                    '<p class="color-debuff">%s %s под эффектом <b>%s</b> и не может атаковать! (осталось %d сек.)</p>',
                    $effect->type->emoji(),
                    $locMonster->monster->name,
                    $effect->type->label(),
                    $remainingSeconds,
                ));

                continue;
            }

            // Чистый стат-дебафф действует до expires_at. Его модификаторы
            // читает MonsterCombatantFactory; действие не зависит от числа
            // нажатых в бою кнопок.
            if ($effect->type === null) {
                continue;
            }

            if ($effect->isDoT()) {
                $lastTickAt = $effect->last_tick_at ?? $effect->applied_at ?? $effect->created_at ?? $now;
                $tickUntil = $effect->expires_at !== null && $effect->expires_at->lt($now)
                    ? $effect->expires_at
                    : $now;
                $tickSeconds = max(1, (int) ($effect->effect?->tick_interval ?: 1));
                $dueTicks = intdiv(max(0, (int) $lastTickAt->diffInSeconds($tickUntil)), $tickSeconds);

                if ($dueTicks > 0) {
                    $damage = (int) $effect->current_value * $dueTicks;
                    // DoT доводит монстра до 1 HP — добивание всегда за игроком.
                    $locMonster->hp_now = max(1, $locMonster->hp_now - $damage);
                    $effect->last_tick_at = $lastTickAt->copy()->addSeconds($dueTicks * $tickSeconds);
                    $effect->stacks = max(0, (int) $effect->stacks - $dueTicks);

                    $result->log(sprintf(
                        '<p class="color-debuff">%s <b>%s</b> от вашего заклинания наносит %s %d урона!</p>',
                        $effect->type->emoji(),
                        $effect->type->label(),
                        $locMonster->monster->name,
                        $damage,
                    ));

                }

                if ($effect->expires_at !== null && $effect->expires_at->lte($now)) {
                    $result->log(sprintf(
                        '<p class="color-info">%s Эффект <b>%s</b> на %s рассеялся.</p>',
                        $effect->type->emoji(),
                        $effect->type->label(),
                        $locMonster->monster->name,
                    ));
                    $effect->delete();
                } elseif ($dueTicks > 0) {
                    $effect->save();
                }
            }
        }

        return $isStunned;
    }

    /**
     * Old battle-bound control rows stored their duration in stacks. Convert
     * them lazily so an in-progress fight survives deployment of timed control.
     */
    private function upgradeLegacyControlExpiration(
        PlayerActiveEffect|MonsterActiveEffect $effect,
        CarbonInterface $now,
    ): void {
        if (! $effect->isControl() || $effect->expires_at !== null) {
            return;
        }

        $startedAt = $effect->applied_at ?? $effect->created_at ?? $now;
        $effect->expires_at = $startedAt->copy()->addSeconds(max(1, (int) $effect->stacks));
        $effect->battle_id = null;
        $effect->save();
    }

    /**
     * Remove all active effects tied to this battle.
     * Use for forced cleanup (e.g. dungeon exit).
     */
    public function cleanupBattleEffects(Battle $battle): void
    {
        $playerIds = PlayerActiveEffect::where('battle_id', $battle->id)
            ->distinct()
            ->pluck('player_id');

        MonsterActiveEffect::where('battle_id', $battle->id)->delete();
        PlayerActiveEffect::where('battle_id', $battle->id)->delete();

        foreach ($playerIds as $playerId) {
            $this->statService->invalidate((int) $playerId);
        }
    }
}
