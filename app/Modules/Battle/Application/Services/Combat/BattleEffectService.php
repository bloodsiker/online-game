<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Domain\Enums\ActiveEffectType;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterActiveEffect;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Domain\Services\PlayerTimedEffectService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;

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
     * Uses effect->slug to resolve the ActiveEffectType.
     */
    public function applyEffectToMonster(
        Effect $effect,
        MonsterOnLocation $locMonster,
        Battle $battle,
        AttackResultDTO $result,
        ?int $tickValueOverride = null,
    ): void {
        $type = ActiveEffectType::tryFrom($effect->slug);

        // Дебафф без соответствующего ActiveEffectType (например, чистый
        // "минус к стату") всё равно должен пройти — иначе строка
        // MonsterActiveEffect никогда не создаётся и MonsterCombatant её не
        // увидит. Игнорируем только по-настоящему неизвестные НЕ-дебаффы.
        if ($type === null && $effect->type !== 'debuff') {
            return; // Неизвестный тип эффекта, не дебафф — игнорируем
        }

        $effectiveDuration = (int) $effect->duration;

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
            $existing->expires_at = now()->addSeconds($effectiveDuration);
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
            'expires_at' => now()->addSeconds($effectiveDuration),
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
        AttackResultDTO $result
    ): void {
        $type = ActiveEffectType::tryFrom($effect->slug);
        // null type = buff with stat_modifiers, no DoT/stun processing needed

        $existing = PlayerActiveEffect::where('player_id', $player->id)
            ->where('effect_id', $effect->id)
            ->whereNull('battle_id')
            ->first();

        $expiresAt = $effect->duration > 0
            ? now()->addSeconds($effect->duration)
            : null;

        if ($existing) {
            $existing->expires_at = $expiresAt;
            $existing->last_tick_at = now();
            $existing->save();
            $this->statService->invalidate($player);

            return;
        }

        PlayerActiveEffect::create([
            'player_id' => $player->id,
            'effect_id' => $effect->id,
            'battle_id' => null,
            'type' => $type,
            'applied_at' => now(),
            'last_tick_at' => now(),
            'expires_at' => $expiresAt,
            'stacks' => 0,
            'current_value' => (float) $effect->value_per_tick,
        ]);

        $this->statService->invalidate($player);
    }

    /**
     * Apply a custom effect (from boss skill JSON config) to the player.
     */
    public function applyCustomEffectToPlayer(
        ActiveEffectType $type,
        float $value,
        int $stacks,
        Player $player,
        Battle $battle,
        AttackResultDTO $result
    ): void {
        $isTimedDoT = $type->isDoT();
        $tickSeconds = max(1, (int) config('game.player_heartbeat_seconds', 10));
        $expiresAt = $isTimedDoT ? now()->addSeconds(max(1, $stacks) * $tickSeconds) : null;

        $existing = PlayerActiveEffect::where('player_id', $player->id)
            ->where('type', $type)
            ->when(
                $isTimedDoT,
                fn ($query) => $query->whereNull('battle_id'),
                fn ($query) => $query->where('battle_id', $battle->id),
            )
            ->first();

        if ($existing) {
            $existing->stacks = $isTimedDoT ? $stacks : max($existing->stacks, $stacks);
            $existing->last_tick_at = now();
            $existing->expires_at = $expiresAt;
            $existing->save();
            $this->statService->invalidate($player);

            return;
        }

        PlayerActiveEffect::create([
            'player_id' => $player->id,
            'battle_id' => $isTimedDoT ? null : $battle->id,
            'type' => $type,
            'applied_at' => now(),
            'last_tick_at' => now(),
            'expires_at' => $expiresAt,
            'stacks' => $stacks,
            'current_value' => $value,
        ]);

        $this->statService->invalidate($player);

        $result->log(sprintf(
            '<p class="color-debuff">%s На вас наложен эффект: <b>%s</b> (%d тиков)</p>',
            $type->emoji(),
            $type->label(),
            $stacks
        ));
    }

    /**
     * Apply a custom effect (например, пассивка «Оглушение» от руны) к монстру.
     */
    public function applyCustomEffectToMonster(
        ActiveEffectType $type,
        float $value,
        int $stacks,
        MonsterOnLocation $locMonster,
        Battle $battle,
        AttackResultDTO $result
    ): void {
        $existing = MonsterActiveEffect::where('location_monster_id', $locMonster->id)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->stacks = max($existing->stacks, $stacks);
            $existing->save();

            return;
        }

        MonsterActiveEffect::create([
            'location_monster_id' => $locMonster->id,
            'battle_id' => $battle->id,
            'type' => $type,
            'applied_at' => now(),
            'stacks' => $stacks,
            'current_value' => $value,
        ]);

        $result->log(sprintf(
            '<p class="color-debuff">%s %s получает эффект: <b>%s</b> (%d ход(а))</p>',
            $type->emoji(),
            $locMonster->monster->name,
            $type->label(),
            $stacks
        ));
    }

    /**
     * Process active effects on the player this round.
     * Returns true if the player is stunned and should skip their attack.
     */
    public function processPlayerEffects(Player $player, Battle $battle, AttackResultDTO $result): bool
    {
        $tickResult = $this->timedEffectService->process($player, now());
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
            if ($effect->isStun()) {
                $isStunned = true;
                $effect->stacks--;

                if ($effect->stacks <= 0) {
                    $result->log('<p class="color-info">💫 Вы больше не оглушены.</p>');
                    $statsChanged = $statsChanged || $effect->effect_id !== null;
                    $effect->delete();
                } else {
                    $result->log(sprintf(
                        '<p class="color-debuff">💫 Вы оглушены и пропускаете ход! (осталось %d ходов)</p>',
                        $effect->stacks
                    ));
                    $effect->save();
                }

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

            if ($effect->isStun()) {
                $isStunned = true;
                $effect->stacks--;

                if ($effect->stacks <= 0) {
                    $result->log(sprintf(
                        '<p class="color-info">💫 %s больше не оглушен.</p>',
                        $locMonster->monster->name
                    ));
                    $effect->delete();
                } else {
                    $result->log(sprintf(
                        '<p class="color-debuff">💫 %s оглушен и пропускает ход! (осталось %d ходов)</p>',
                        $locMonster->monster->name,
                        $effect->stacks
                    ));
                    $effect->save();
                }

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
