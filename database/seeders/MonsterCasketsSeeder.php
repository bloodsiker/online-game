<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class MonsterCasketsSeeder extends Seeder
{
    private const FALLBACK_IMAGE = '/img/bank_stock/mithril_chest.gif';

    /**
     * @var list<array{
     *     name: string,
     *     min_level: int,
     *     max_level: int|null,
     *     difficulty: int,
     *     duration: int,
     *     rarity: string,
     *     source_image: string,
     *     trap_penalty: int,
     *     trap_effect: string|null,
     *     trap_duration: int,
     *     trap_damage: int
     * }>
     */
    private const CASKETS = [
        [
            'name' => 'Потрёпанная шкатулка',
            'min_level' => 1,
            'max_level' => 14,
            'difficulty' => 20,
            'duration' => 10,
            'rarity' => 'common',
            'source_image' => 'Медный ларец',
            'trap_penalty' => 0,
            'trap_effect' => null,
            'trap_duration' => 60,
            'trap_damage' => 0,
        ],
        [
            'name' => 'Медная шкатулка',
            'min_level' => 15,
            'max_level' => 29,
            'difficulty' => 70,
            'duration' => 14,
            'rarity' => 'uncommon',
            'source_image' => 'Железный ларец',
            'trap_penalty' => 5,
            'trap_effect' => 'Слабость',
            'trap_duration' => 90,
            'trap_damage' => 3,
        ],
        [
            'name' => 'Стальная шкатулка',
            'min_level' => 30,
            'max_level' => 44,
            'difficulty' => 120,
            'duration' => 18,
            'rarity' => 'rare',
            'source_image' => 'Закалённый ларец',
            'trap_penalty' => 8,
            'trap_effect' => 'Слабость',
            'trap_duration' => 120,
            'trap_damage' => 5,
        ],
        [
            'name' => 'Руническая шкатулка',
            'min_level' => 45,
            'max_level' => 59,
            'difficulty' => 170,
            'duration' => 22,
            'rarity' => 'epic',
            'source_image' => 'Рунический сейф',
            'trap_penalty' => 10,
            'trap_effect' => 'Разрыв брони',
            'trap_duration' => 150,
            'trap_damage' => 7,
        ],
        [
            'name' => 'Древняя шкатулка',
            'min_level' => 60,
            'max_level' => 69,
            'difficulty' => 220,
            'duration' => 26,
            'rarity' => 'legendary',
            'source_image' => 'Сундук древних',
            'trap_penalty' => 13,
            'trap_effect' => 'Разрыв брони',
            'trap_duration' => 180,
            'trap_damage' => 9,
        ],
        [
            'name' => 'Шкатулка Бездны',
            'min_level' => 70,
            'max_level' => null,
            'difficulty' => 270,
            'duration' => 30,
            'rarity' => 'heroic',
            'source_image' => 'Рунный ковчег',
            'trap_penalty' => 16,
            'trap_effect' => 'Разрыв брони',
            'trap_duration' => 210,
            'trap_damage' => 12,
        ],
    ];

    private const LOOT_NAMES = [
        1 => ['Железный слиток', 'Малое зелье лечения', 'Грубая отмычка'],
        2 => ['Кристалл возвышения', 'Зелье лечения', 'Усиленная отмычка'],
        3 => ['Сердце возвышения', 'Крепкое зелье лечения', 'Стальная отмычка'],
        4 => ['Реликтовое ядро', 'Великое зелье лечения', 'Точная отмычка'],
        5 => ['Красный Камень Печати', 'Легендарное зелье лечения', 'Мастерская отмычка'],
        6 => ['Алмазная колба', 'Героическое зелье лечения', 'Рунная отмычка'],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (self::CASKETS as $index => $definition) {
                $tier = $index + 1;
                $casketId = $this->upsertCasket($definition, $tier);
                $this->upsertLockConfig($casketId, $definition);
                $this->syncLoot($casketId, $tier);
                $this->syncMonsterDrops($casketId, $definition);
            }
        });

        $this->command?->info('Созданы шкатулки из дропа монстров: 6 уровневых тиров.');
    }

    /** @param array<string, int|string|null> $definition */
    private function upsertCasket(array $definition, int $tier): int
    {
        $existing = DB::table('share_items')->where('name', $definition['name'])->first();
        $image = $existing?->image
            ?: DB::table('share_items')->where('name', $definition['source_image'])->value('image')
            ?: self::FALLBACK_IMAGE;
        $levelRange = $definition['max_level'] === null
            ? "с {$definition['min_level']} уровня"
            : "{$definition['min_level']}–{$definition['max_level']} уровней";
        $attributes = [
            'description' => sprintf(
                'Трофейная шкатулка %d тира. Выпадает с монстров %s. Её нужно поднять с земли, после чего можно открыть из рюкзака. Сложность замка: %d.',
                $tier,
                $levelRange,
                $definition['difficulty'],
            ),
            'type' => 'chest',
            'rarity' => $definition['rarity'],
            'image' => $image,
            'max_drop_level_difference' => 10,
            'drop_direct_to_backpack' => false,
            'is_active' => true,
            'is_sell' => false,
            'is_give' => true,
            'is_droppable' => true,
            'is_stackable' => false,
            'is_weight' => true,
            'price' => 0,
            'updated_at' => now(),
        ];

        if ($existing === null) {
            return (int) DB::table('share_items')->insertGetId([
                'name' => $definition['name'],
                ...$attributes,
                'created_at' => now(),
            ]);
        }

        DB::table('share_items')->where('id', $existing->id)->update($attributes);

        return (int) $existing->id;
    }

    /** @param array<string, int|string|null> $definition */
    private function upsertLockConfig(int $casketId, array $definition): void
    {
        $effectId = null;
        if ($definition['trap_effect'] !== null) {
            $effectId = DB::table('effects')->where('name', $definition['trap_effect'])->value('id');
            if ($effectId === null) {
                throw new RuntimeException("Не найден эффект ловушки «{$definition['trap_effect']}».");
            }
        }

        DB::table('share_item_lock_configs')->updateOrInsert(
            ['share_item_id' => $casketId],
            [
                'lock_required_skill' => $definition['difficulty'],
                'lock_duration_seconds' => $definition['duration'],
                'experience_reward' => max(2, (int) ceil(((int) $definition['difficulty']) / 10)),
                'trap_chance_penalty_percent' => $definition['trap_penalty'],
                'trap_effect_id' => $effectId,
                'trap_effect_duration_seconds' => $definition['trap_duration'],
                'trap_damage_percent' => $definition['trap_damage'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    private function syncLoot(int $casketId, int $tier): void
    {
        $lootIds = collect(self::LOOT_NAMES[$tier])
            ->mapWithKeys(fn (string $name): array => [$name => DB::table('share_items')->where('name', $name)->value('id')]);
        if ($lootIds->contains(null)) {
            $missing = $lootIds->search(null, true);

            throw new RuntimeException("Не найден предмет «{$missing}» для содержимого шкатулки.");
        }

        $existingLoot = DB::table('share_item_has_items')->where('parent_item_id', $casketId);
        if (Schema::hasTable('share_item_instant_rewards')) {
            $existingLoot->whereNotIn(
                'share_item_id',
                DB::table('share_item_instant_rewards')->select('share_item_id'),
            );
        }
        $existingLoot->delete();
        DB::table('share_item_has_items')->insert([
            [
                'parent_item_id' => $casketId,
                'share_item_id' => $lootIds[self::LOOT_NAMES[$tier][0]],
                'min_count' => 1,
                'max_count' => max(1, (int) ceil($tier / 2)),
                'drop_chance' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_item_id' => $casketId,
                'share_item_id' => $lootIds[self::LOOT_NAMES[$tier][1]],
                'min_count' => 1,
                'max_count' => 1,
                'drop_chance' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_item_id' => $casketId,
                'share_item_id' => $lootIds[self::LOOT_NAMES[$tier][2]],
                'min_count' => 1,
                'max_count' => 1,
                'drop_chance' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /** @param array<string, int|string|null> $definition */
    private function syncMonsterDrops(int $casketId, array $definition): void
    {
        $monsters = DB::table('monsters')
            ->where('lvl', '>=', $definition['min_level'])
            ->when(
                $definition['max_level'] !== null,
                fn ($query) => $query->where('lvl', '<=', $definition['max_level']),
            )
            ->where('name', 'not like', 'ТЕСТ%')
            ->get(['id', 'is_boss']);

        DB::table('monster_has_items')->where('share_item_id', $casketId)->delete();
        if ($monsters->isEmpty()) {
            return;
        }

        $now = now();
        DB::table('monster_has_items')->insert($monsters->map(
            static fn (object $monster): array => [
                'monster_id' => $monster->id,
                'share_item_id' => $casketId,
                'drop_chance' => $monster->is_boss ? 5.0 : 2.0,
                'min_count' => 1,
                'max_count' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        )->all());
    }
}
