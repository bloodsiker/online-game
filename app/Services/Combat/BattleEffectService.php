<?php

declare(strict_types=1);

namespace App\Services\Combat;

use App\DTO\AttackResultDTO;
use App\Enums\ActiveEffectType;
use App\Models\Battle\Battle;
use App\Models\MagicSkill\Effect;
use App\Models\Monster\MonsterActiveEffect;
use App\Models\Monster\MonsterOnLocation;
use App\Models\Player\Player;
use App\Models\Player\PlayerActiveEffect;

class BattleEffectService
{
    /**
     * Apply an Effect model (from player magic skill) to the monster.
     * Uses effect->slug to resolve the ActiveEffectType.
     */
    public function applyEffectToMonster(
        Effect $effect,
        MonsterOnLocation $locMonster,
        Battle $battle,
        AttackResultDTO $result
    ): void {
        $type = ActiveEffectType::tryFrom($effect->slug);

        if ($type === null) {
            return; // Неизвестный тип эффекта — игнорируем
        }

        $existing = MonsterActiveEffect::where('location_monster_id', $locMonster->id)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->stacks = max($existing->stacks, (int) $effect->duration);
            $existing->save();

            return;
        }

        MonsterActiveEffect::create([
            'location_monster_id' => $locMonster->id,
            'effect_id' => $effect->id,
            'battle_id' => $battle->id,
            'type' => $type,
            'applied_at' => now(),
            'stacks' => (int) $effect->duration,
            'current_value' => (float) $effect->value_per_tick,
        ]);
    }

    /**
     * Apply an Effect model (from player buff magic skill) to the player himself.
     */
    /**
     * Apply an Effect model to a player.
     *
     * In-battle ($battle set): duration = turns (stacks countdown each round).
     * Out-of-battle ($battle null): duration = seconds (expires_at based).
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
            ->first();

        if ($battle !== null) {
            // In-battle: turn-based stacks
            if ($existing) {
                $existing->stacks = max($existing->stacks ?? 0, (int) $effect->duration);
                $existing->save();

                return;
            }

            PlayerActiveEffect::create([
                'player_id' => $player->id,
                'effect_id' => $effect->id,
                'battle_id' => $battle->id,
                'type' => $type,
                'applied_at' => now(),
                'stacks' => (int) $effect->duration,
                'current_value' => (float) $effect->value_per_tick,
            ]);
        } else {
            // Out-of-battle: time-based expiry
            $expiresAt = $effect->duration > 0
                ? now()->addSeconds($effect->duration)
                : null;

            if ($existing) {
                $existing->expires_at = $expiresAt;
                $existing->save();

                return;
            }

            PlayerActiveEffect::create([
                'player_id' => $player->id,
                'effect_id' => $effect->id,
                'battle_id' => null,
                'type' => $type,
                'applied_at' => now(),
                'expires_at' => $expiresAt,
                'stacks' => 0,
                'current_value' => (float) $effect->value_per_tick,
            ]);
        }
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
        $existing = PlayerActiveEffect::where('player_id', $player->id)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->stacks = max($existing->stacks, $stacks);
            $existing->save();

            return;
        }

        PlayerActiveEffect::create([
            'player_id' => $player->id,
            'battle_id' => $battle->id,
            'type' => $type,
            'applied_at' => now(),
            'stacks' => $stacks,
            'current_value' => $value,
        ]);

        $result->log(sprintf(
            '<p class="color-debuff">%s На вас наложен эффект: <b>%s</b> (%d ходов)</p>',
            $type->emoji(),
            ucfirst($type->value),
            $stacks
        ));
    }

    /**
     * Process active effects on the player this round.
     * Returns true if the player is stunned and should skip their attack.
     */
    public function processPlayerEffects(Player $player, Battle $battle, AttackResultDTO $result): bool
    {
        $effects = PlayerActiveEffect::where('player_id', $player->id)->get();

        $isStunned = false;

        foreach ($effects as $effect) {
            if ($effect->isStun()) {
                $isStunned = true;
                $effect->stacks--;

                if ($effect->stacks <= 0) {
                    $result->log('<p class="color-info">💫 Вы больше не оглушены.</p>');
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
                $damage = (int) $effect->current_value;
                $player->hp_now = max(0, $player->hp_now - $damage);

                $result->log(sprintf(
                    '<p class="color-debuff">%s <b>%s</b> наносит вам %d урона!</p>',
                    $effect->type->emoji(),
                    ucfirst($effect->type->value),
                    $damage
                ));

                $effect->stacks--;
                $effect->stacks <= 0 ? $effect->delete() : $effect->save();

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
                $effect->stacks <= 0 ? $effect->delete() : $effect->save();
            }
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
        $effects = MonsterActiveEffect::where('location_monster_id', $locMonster->id)->get();

        $isStunned = false;

        foreach ($effects as $effect) {
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

            if ($effect->isDoT()) {
                $damage = (int) $effect->current_value;
                // DoT доводит монстра до 1 HP — добивание всегда за игроком
                $locMonster->hp_now = max(1, $locMonster->hp_now - $damage);

                $result->log(sprintf(
                    '<p class="color-debuff">%s <b>%s</b> от вашего заклинания наносит %s %d урона!</p>',
                    $effect->type->emoji(),
                    ucfirst($effect->type->value),
                    $locMonster->monster->name,
                    $damage
                ));

                $effect->stacks--;

                if ($effect->stacks <= 0) {
                    $result->log(sprintf(
                        '<p class="color-info">%s Эффект <b>%s</b> на %s рассеялся.</p>',
                        $effect->type->emoji(),
                        ucfirst($effect->type->value),
                        $locMonster->monster->name
                    ));
                    $effect->delete();
                } else {
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
        MonsterActiveEffect::where('battle_id', $battle->id)->delete();
        PlayerActiveEffect::where('battle_id', $battle->id)->delete();
    }
}
