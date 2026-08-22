<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Dungeon\Domain\Enums\DungeonCooldownType;
use App\Modules\Dungeon\Domain\Enums\DungeonRewardType;
use App\Modules\Dungeon\Domain\Enums\DungeonType;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Данж «Арена Испытания» — тип survival, 5 волн, одна локация-арена.
 *
 * Пул монстров: Мышь (monster_id=1) и Летучая мышь (monster_id=2).
 * Каждая волна спавнит count_monster=4 случайных монстра из пула.
 * Волны 1–5 одинаковы по составу, нарастание сложности — через HP монстров.
 *
 * Вход и выход: локация id=6 (город).
 */
class SurvivalArenaSeeder extends Seeder
{
    private const WAVE_COUNT = 5;

    private const MONSTERS_PER_WAVE = 4;

    private const ENTRY_LOCATION = 6;

    private const MONSTER_WEAK = 1;  // Мышь

    private const MONSTER_STRONG = 2;  // Летучая мышь

    private const XP_MULTIPLIER = 2.0; // x2 опыт за монстров

    private const REWARD_GOLD_MIN = 200;

    private const REWARD_GOLD_MAX = 500;

    private const REWARD_XP = 300;

    public function run(): void
    {
        if (Dungeon::where('name', 'Арена Испытания')->exists()) {
            $this->command->info('SurvivalArenaSeeder: уже существует, пропускаем.');

            return;
        }

        DB::transaction(function () {
            // ── 1. Данж ──────────────────────────────────────────────────────
            $dungeon = Dungeon::create([
                'name' => 'Арена Испытания',
                'description' => 'Тёмная арена. Каждая победа призывает новую волну. Выживи все '.self::WAVE_COUNT.'.',
                'tier' => 1,
                'type' => DungeonType::SURVIVAL,
                'wave_count' => self::WAVE_COUNT,
                'xp_multiplier' => self::XP_MULTIPLIER,
                'entry_share_item_id' => null,
                'max_players' => 1,
                'cooldown_type' => DungeonCooldownType::PERSONAL,
                'cooldown_seconds' => 3600,
                'time_limit_seconds' => 900,
                'min_level' => 1,
                'is_active' => true,
                'monster_respawn' => false,
                'entry_location_id' => self::ENTRY_LOCATION,
                'return_location_id' => self::ENTRY_LOCATION,
                'map_id' => null,
            ]);

            // ── 2. Локация-арена (одна комната) ──────────────────────────────
            $arena = Location::create([
                'name' => 'Арена Испытания',
                'description' => 'Круглая каменная арена. Запах крови не выветривается.',
                'dungeon_id' => $dungeon->id,
                'map_id' => null,
                'time_not_attack' => 1,
                'percent_respawn_monster' => 0,
                'count_monster' => self::MONSTERS_PER_WAVE,
            ]);

            // ── 3. Обновить данж: арена = вход, выход и exit ─────────────────
            $dungeon->update([
                'first_location_id' => $arena->id,
                'exit_location_id' => $arena->id,
            ]);

            // ── 4. Пул монстров волны ─────────────────────────────────────────
            DB::table('location_has_monsters')->insert([
                ['location_id' => $arena->id, 'monster_id' => self::MONSTER_WEAK],
                ['location_id' => $arena->id, 'monster_id' => self::MONSTER_STRONG],
            ]);

            // ── 5. Награды за прохождение ─────────────────────────────────────
            $dungeon->rewards()->createMany([
                [
                    'type' => DungeonRewardType::GOLD,
                    'amount_min' => self::REWARD_GOLD_MIN,
                    'amount_max' => self::REWARD_GOLD_MAX,
                    'drop_chance' => 100.0,
                ],
                [
                    'type' => DungeonRewardType::EXPERIENCE,
                    'amount_min' => self::REWARD_XP,
                    'amount_max' => self::REWARD_XP,
                    'drop_chance' => 100.0,
                ],
            ]);

            $this->command->info(sprintf(
                'SurvivalArenaSeeder: создана «%s» (dungeon_id=%d, arena_id=%d) — %d волн × %d монстров.',
                $dungeon->name,
                $dungeon->id,
                $arena->id,
                self::WAVE_COUNT,
                self::MONSTERS_PER_WAVE,
            ));
        });
    }
}
