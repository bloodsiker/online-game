<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DungeonCooldownType;
use App\Enums\DungeonType;
use App\Models\Dungeon\Dungeon;
use App\Models\Dungeon\DungeonGate;
use App\Models\Location\Location;
use App\Models\Map;
use App\Models\Monster\MonsterOnLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Данж "Тёмное Подземелье" — 20 локаций, 3 зоны.
 *
 * Зона 1 (D1–D6): Коридоры Теней — Мышь (monster_id=1), 6 локаций
 * Зона 2 (D7–D14): Катакомбы — Летучая мышь (monster_id=2), 8 локаций
 * Зона 3 (D15–D20): Логово Босса — Летучая мышь (monster_id=2), 6 локаций
 *
 * Ворота 1: D6 → D7 (area_cleared — убить всех монстров на D6)
 * Ворота 2: D14 → D15 (area_cleared — убить всех монстров на D14)
 *
 * Вход с локации id=6. D20 — выход из данжа.
 */
class DungeonSeeder extends Seeder
{
    public function run(): void
    {
        if (Dungeon::where('name', 'Тёмное Подземелье')->exists()) {
            $this->command->info('DungeonSeeder: уже существует, пропускаем.');
            return;
        }

        DB::transaction(function () {
            // ── 1. Создать данж (без location IDs, обновим позже) ──────────────
            $dungeon = Dungeon::create([
                'name'                => 'Тёмное Подземелье',
                'description'         => 'Заброшенные катакомбы, полные мышей и летучих тварей.',
                'tier'                => 1,
                'type'                => DungeonType::LINEAR,
                'entry_share_item_id' => null,
                'max_players'         => 1,
                'cooldown_type'       => DungeonCooldownType::PERSONAL,
                'cooldown_seconds'    => 86400,
                'time_limit_seconds'  => 3600,
                'min_level'           => 1,
                'is_active'           => true,
                'monster_respawn'     => false,
                'entry_location_id'   => 6,
                'return_location_id'  => 6,
            ]);

            // ── 2. Создать карту данжа ─────────────────────────────────────────
            $map = new Map();
            $map->name              = 'Тёмное Подземелье';
            $map->folder            = 'dungeon';
            $map->slug              = 'dark-dungeon-' . $dungeon->id;
            $map->resp_location_id  = 6;
            $map->save();

            $dungeon->map_id = $map->id;
            $dungeon->save();

            // ── 3. Создать 20 локаций ──────────────────────────────────────────
            $base = [
                'map_id'                  => $map->id,
                'dungeon_id'              => $dungeon->id,
                'time_not_attack'         => 1,
                'percent_respawn_monster' => 0,
                'last_respawn_monster_at' => null,
            ];

            // Зона 1 — Коридоры Теней
            $d1  = $this->mkLoc('Тёмный Вход',           $base, 2);
            $d2  = $this->mkLoc('Узкий Коридор',          $base, 2);
            $d3  = $this->mkLoc('Заброшенная Ниша',       $base, 2);
            $d4  = $this->mkLoc('Зал Стражников',         $base, 3);
            $d5  = $this->mkLoc('Тёмный Закоулок',        $base, 2);
            $d6  = $this->mkLoc('Перекрёсток Теней',      $base, 3);  // ворота 1

            // Зона 2 — Катакомбы
            $d7  = $this->mkLoc('Катакомбы',              $base, 3);
            $d8  = $this->mkLoc('Лабиринт Склепов',       $base, 3);
            $d9  = $this->mkLoc('Тупик Призраков',        $base, 2);
            $d10 = $this->mkLoc('Зал Призывателей',       $base, 2);
            $d11 = $this->mkLoc('Подземный Мост',         $base, 3);
            $d12 = $this->mkLoc('Логово Нетопырей',       $base, 2);
            $d13 = $this->mkLoc('Тронный Зал',            $base, 3);
            $d14 = $this->mkLoc('Врата Тьмы',             $base, 4);  // ворота 2

            // Зона 3 — Логово Босса
            $d15 = $this->mkLoc('Покои Тьмы',             $base, 3);
            $d16 = $this->mkLoc('Темница',                $base, 2);
            $d17 = $this->mkLoc('Зал Пыток',              $base, 3);
            $d18 = $this->mkLoc('Камера Приготовлений',   $base, 2);
            $d19 = $this->mkLoc('Логово Босса',           $base, 4);
            $d20 = $this->mkLoc('Выход из Подземелья',    $base, 0);

            // ── 4. Соединить локации ─────────────────────────────────────────
            // Зона 1: D1 →east D2 →east D4 →east D5 →east D6
            //                    ↓south D3
            $d1->east  = $d2->id;  $d1->save();
            $d2->west  = $d1->id;  $d2->east  = $d4->id;  $d2->south = $d3->id; $d2->save();
            $d3->north = $d2->id;  $d3->save();
            $d4->west  = $d2->id;  $d4->east  = $d5->id;  $d4->save();
            $d5->west  = $d4->id;  $d5->east  = $d6->id;  $d5->save();
            $d6->west  = $d5->id;  $d6->east  = $d7->id;  $d6->save(); // ворота 1: east→D7

            // Зона 2: D7 →east D8 →east D11 →east D13 →east D14
            //          ↓south D9  ↓south D10  ↓south D12
            $d7->west  = $d6->id;  $d7->east  = $d8->id;  $d7->south = $d9->id;  $d7->save();
            $d8->west  = $d7->id;  $d8->east  = $d11->id; $d8->south = $d10->id; $d8->save();
            $d9->north = $d7->id;  $d9->save();
            $d10->north = $d8->id; $d10->save();
            $d11->west = $d8->id;  $d11->east = $d13->id; $d11->south = $d12->id; $d11->save();
            $d12->north = $d11->id; $d12->save();
            $d13->west = $d11->id; $d13->east = $d14->id; $d13->save();
            $d14->west = $d13->id; $d14->east = $d15->id; $d14->save(); // ворота 2: east→D15

            // Зона 3: D15 →east D17 →east D18 →east D20 (выход)
            //          ↓south D16           ↓south D19 (босс)
            $d15->west = $d14->id; $d15->east = $d17->id; $d15->south = $d16->id; $d15->save();
            $d16->north = $d15->id; $d16->save();
            $d17->west = $d15->id; $d17->east = $d18->id; $d17->save();
            $d18->west = $d17->id; $d18->east = $d20->id; $d18->south = $d19->id; $d18->save();
            $d19->north = $d18->id; $d19->save();
            $d20->west = $d18->id;  $d20->save();

            // ── 5. Обновить first/exit в данже ───────────────────────────────
            $dungeon->first_location_id = $d1->id;
            $dungeon->exit_location_id  = $d20->id;
            $dungeon->save();

            // ── 6. Создать ворота ─────────────────────────────────────────────
            DungeonGate::create([
                'dungeon_id'       => $dungeon->id,
                'from_location_id' => $d6->id,
                'to_location_id'   => $d7->id,
                'unlock_type'      => 'area_cleared',
            ]);

            DungeonGate::create([
                'dungeon_id'       => $dungeon->id,
                'from_location_id' => $d14->id,
                'to_location_id'   => $d15->id,
                'unlock_type'      => 'area_cleared',
            ]);

            // ── 7. location_has_monsters ──────────────────────────────────────
            // Зона 1: Мышь (monster_id=1)
            foreach ([$d1, $d2, $d3, $d4, $d5, $d6] as $loc) {
                DB::table('location_has_monsters')->insert([
                    'location_id' => $loc->id,
                    'monster_id'  => 1,
                ]);
            }

            // Зона 2 + 3: Летучая мышь (monster_id=2)
            foreach ([$d7, $d8, $d9, $d10, $d11, $d12, $d13, $d14, $d15, $d16, $d17, $d18, $d19] as $loc) {
                DB::table('location_has_monsters')->insert([
                    'location_id' => $loc->id,
                    'monster_id'  => 2,
                ]);
            }

            // ── 8. Заспавнить монстров ────────────────────────────────────────
            // Зона 1: Мышь — hp=100
            foreach ([$d1, $d2, $d3, $d4, $d5, $d6] as $loc) {
                for ($i = 0; $i < $loc->count_monster; $i++) {
                    MonsterOnLocation::create([
                        'location_id'   => $loc->id,
                        'monster_id'    => 1,
                        'hp_max'        => 100,
                        'hp_now'        => 100,
                        'active'        => 1,
                        'is_drop_money' => 1,
                        'current_phase' => 1,
                    ]);
                }
            }

            // Зона 2 + 3: Летучая мышь — hp=150
            foreach ([$d7, $d8, $d9, $d10, $d11, $d12, $d13, $d14, $d15, $d16, $d17, $d18, $d19] as $loc) {
                for ($i = 0; $i < $loc->count_monster; $i++) {
                    MonsterOnLocation::create([
                        'location_id'   => $loc->id,
                        'monster_id'    => 2,
                        'hp_max'        => 150,
                        'hp_now'        => 150,
                        'active'        => 1,
                        'is_drop_money' => 1,
                        'current_phase' => 1,
                    ]);
                }
            }

            $this->command->info("DungeonSeeder: создан данж «{$dungeon->name}» (id={$dungeon->id}), 20 локаций.");
        });
    }

    private function mkLoc(string $name, array $base, int $countMonster): Location
    {
        return Location::create(array_merge($base, [
            'name'          => $name,
            'count_monster' => $countMonster,
        ]));
    }
}