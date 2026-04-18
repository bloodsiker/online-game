<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MagicSkill\Effect;
use App\Models\MagicSkill\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Создаёт бафф/хил заклинания с эффектами и выдаёт их игроку id=1.
 *
 * Скиллы:
 *  1. Боевой клич      — buff, +30% атаки на 5 мин
 *  2. Каменная кожа    — buff, +50% брони на 3 мин
 *  3. Благословение    — buff, +20% HP_max + +20% MP_max на 10 мин
 *  4. Малое исцеление  — heal, восстанавливает 80 HP
 *  5. Великое исцеление — heal, восстанавливает 200 HP
 *  6. Регенерация      — buff (DoT heal), +15 HP каждый ход в бою, 4 хода
 */
class BuffSkillSeeder extends Seeder
{
    private const PLAYER_ID = 1;

    public function run(): void
    {
        if (MagicSkill::where('slug', 'battle_cry')->exists()) {
            $this->command->info('BuffSkillSeeder: уже существует, пропускаем.');

            return;
        }

        DB::transaction(function () {
            // ── Effects ───────────────────────────────────────────────────────

            $effectAttack = Effect::create([
                'name' => 'Боевой клич',
                'slug' => 'battle_cry',
                'type' => 'buff',
                'description' => '+30% к атаке',
                'duration' => 300, // секунд вне боя
                'is_stackable' => false,
                'max_stacks' => 1,
                'tick_interval' => 1,
                'value_per_tick' => 0,
                'stat_modifiers' => [
                    ['type' => 'attack', 'value' => 30, 'is_percent' => true],
                ],
                'is_dispellable' => true,
            ]);

            $effectArmor = Effect::create([
                'name' => 'Каменная кожа',
                'slug' => 'stone_skin',
                'type' => 'buff',
                'description' => '+50% к броне',
                'duration' => 180,
                'is_stackable' => false,
                'max_stacks' => 1,
                'tick_interval' => 1,
                'value_per_tick' => 0,
                'stat_modifiers' => [
                    ['type' => 'armor', 'value' => 50, 'is_percent' => true],
                ],
                'is_dispellable' => true,
            ]);

            $effectBlessing = Effect::create([
                'name' => 'Благословение',
                'slug' => 'blessing',
                'type' => 'buff',
                'description' => '+20% HP и MP',
                'duration' => 600,
                'is_stackable' => false,
                'max_stacks' => 1,
                'tick_interval' => 1,
                'value_per_tick' => 0,
                'stat_modifiers' => [
                    ['type' => 'hp_max', 'value' => 20, 'is_percent' => true],
                    ['type' => 'mp_max', 'value' => 20, 'is_percent' => true],
                ],
                'is_dispellable' => true,
            ]);

            $effectRegen = Effect::create([
                'name' => 'Регенерация',
                'slug' => 'regen',
                'type' => 'buff',
                'description' => '+15 HP каждый ход (4 хода)',
                'duration' => 4,   // ходов в бою (stacks)
                'is_stackable' => false,
                'max_stacks' => 1,
                'tick_interval' => 1,
                'value_per_tick' => 15,  // heal per tick
                'stat_modifiers' => null,
                'is_dispellable' => true,
            ]);

            // ── Skills ────────────────────────────────────────────────────────

            $battleCry = MagicSkill::create([
                'name' => 'Боевой клич',
                'slug' => 'battle_cry',
                'description' => 'Воодушевляет союзников, увеличивая атаку на 30% на 5 минут.',
                'type' => 'buff',
                'target_type' => 'all',
                'mana_cost' => 20,
                'min_damage' => 0,
                'max_damage' => 0,
                'base_healing' => 0,
                'cooldown' => 60,
                'level' => 1,
                'is_passive' => false,
            ]);
            $battleCry->skillEffects()->attach($effectAttack->id, ['chance' => 100]);

            $stoneSkin = MagicSkill::create([
                'name' => 'Каменная кожа',
                'slug' => 'stone_skin',
                'description' => 'Покрывает кожу камнем, увеличивая броню на 50% на 3 минуты.',
                'type' => 'buff',
                'target_type' => 'self',
                'mana_cost' => 25,
                'min_damage' => 0,
                'max_damage' => 0,
                'base_healing' => 0,
                'cooldown' => 90,
                'level' => 1,
                'is_passive' => false,
            ]);
            $stoneSkin->skillEffects()->attach($effectArmor->id, ['chance' => 100]);

            $blessing = MagicSkill::create([
                'name' => 'Благословение',
                'slug' => 'blessing',
                'description' => 'Благословляет цель, увеличивая максимум HP и MP на 20% на 10 минут.',
                'type' => 'buff',
                'target_type' => 'all',
                'mana_cost' => 35,
                'min_damage' => 0,
                'max_damage' => 0,
                'base_healing' => 0,
                'cooldown' => 120,
                'level' => 1,
                'is_passive' => false,
            ]);
            $blessing->skillEffects()->attach($effectBlessing->id, ['chance' => 100]);

            $minorHeal = MagicSkill::create([
                'name' => 'Малое исцеление',
                'slug' => 'minor_heal',
                'description' => 'Восстанавливает 80 HP цели.',
                'type' => 'heal',
                'target_type' => 'all',
                'mana_cost' => 15,
                'min_damage' => 0,
                'max_damage' => 0,
                'base_healing' => 80,
                'cooldown' => 30,
                'level' => 1,
                'is_passive' => false,
            ]);

            $majorHeal = MagicSkill::create([
                'name' => 'Великое исцеление',
                'slug' => 'major_heal',
                'description' => 'Восстанавливает 200 HP цели.',
                'type' => 'heal',
                'target_type' => 'all',
                'mana_cost' => 40,
                'min_damage' => 0,
                'max_damage' => 0,
                'base_healing' => 200,
                'cooldown' => 60,
                'level' => 3,
                'is_passive' => false,
            ]);

            $regen = MagicSkill::create([
                'name' => 'Регенерация',
                'slug' => 'regen_skill',
                'description' => 'Накладывает регенерацию: +15 HP каждый ход в течение 4 ходов.',
                'type' => 'buff',
                'target_type' => 'all',
                'mana_cost' => 18,
                'min_damage' => 0,
                'max_damage' => 0,
                'base_healing' => 0,
                'cooldown' => 45,
                'level' => 1,
                'is_passive' => false,
            ]);
            $regen->skillEffects()->attach($effectRegen->id, ['chance' => 100]);

            // ── Выдать игроку id=1 ─────────────────────────────────────────────

            $player = Player::find(self::PLAYER_ID);

            if (! $player) {
                $this->command->warn('BuffSkillSeeder: игрок id=1 не найден, скиллы не выданы.');

                return;
            }

            $skillIds = [
                $battleCry->id,
                $stoneSkin->id,
                $blessing->id,
                $minorHeal->id,
                $majorHeal->id,
                $regen->id,
            ];

            foreach ($skillIds as $skillId) {
                $player->magicSkills()->attach($skillId, [
                    'is_equipped' => false,
                    'cooldown_end_at' => null,
                ]);
            }

            $this->command->info(sprintf(
                'BuffSkillSeeder: создано %d скиллов + %d эффектов → выданы игроку id=%d (%s).',
                count($skillIds),
                4,
                self::PLAYER_ID,
                $player->name,
            ));
        });
    }
}
