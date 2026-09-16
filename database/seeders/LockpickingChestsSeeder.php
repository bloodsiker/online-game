<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LockpickingChestsSeeder extends Seeder
{
    private const LOCATION_ID = 6;

    private const CHEST_IMAGE = 'items/ED1oFeQjGH464xyEnNyVmFcoW9rMFgS2XhV0jl23.webp';

    private const CHESTS = [
        [1, 0, 10, 'Простой запертый сундук'],
        [1, 1, 25, 'Медный ларец'],
        [1, 2, 45, 'Сундук странника'],
        [2, 0, 60, 'Укреплённый сундук'],
        [2, 1, 80, 'Железный сейф'],
        [2, 2, 95, 'Запечатанный сундук'],
        [3, 0, 110, 'Стальной сундук'],
        [3, 1, 130, 'Сундук дозорного'],
        [3, 2, 145, 'Закалённый ларец'],
        [4, 0, 160, 'Сундук тайной стражи'],
        [4, 1, 180, 'Рунический сейф'],
        [4, 2, 195, 'Проклятый ковчег'],
        [5, 0, 210, 'Сундук древних'],
        [5, 1, 230, 'Сокровищница магистра'],
        [5, 2, 245, 'Запретный ковчег'],
        [6, 0, 260, 'Рунный ковчег'],
        [6, 1, 280, 'Сундук владыки'],
        [6, 2, 300, 'Хранилище Бездны'],
    ];

    private const RARITIES = [
        1 => 'common',
        2 => 'uncommon',
        3 => 'rare',
        4 => 'epic',
        5 => 'legendary',
        6 => 'heroic',
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
            $weaknessEffectId = $this->effectId('Слабость');
            $armorBreakEffectId = $this->effectId('Разрыв брони');

            foreach (self::CHESTS as [$tier, $variant, $difficulty, $name]) {
                $chest = $this->upsertChest($tier, $variant, $difficulty, $name);
                $this->upsertConfig($chest, $tier, $variant, $difficulty, $weaknessEffectId, $armorBreakEffectId);
                $this->syncLoot($chest, $tier, $variant);
                $this->ensureLocationPlacement($chest);
            }
        });
    }

    private function upsertChest(int $tier, int $variant, int $difficulty, string $name): ShareItem
    {
        $chest = ShareItem::query()->where('name', $name)->first() ?? new ShareItem;
        $chest->forceFill([
            'name' => $name,
            'description' => sprintf(
                'Запертый сундук %d тира. Сложность замка: %d. %s',
                $tier,
                $difficulty,
                match ($variant) {
                    0 => 'Не защищён ловушкой.',
                    1 => 'Защищён ослабляющей ловушкой.',
                    default => 'Защищён усиленной ловушкой.',
                },
            ),
            'type' => 'chest',
            'rarity' => self::RARITIES[$tier],
            'image' => $chest->getRawOriginal('image') ?: self::CHEST_IMAGE,
            'is_active' => true,
            'is_sell' => false,
            'is_give' => true,
            'is_droppable' => true,
            'is_stackable' => false,
            'is_weight' => true,
            'price' => 0,
        ])->save();

        return $chest->refresh();
    }

    private function upsertConfig(
        ShareItem $chest,
        int $tier,
        int $variant,
        int $difficulty,
        int $weaknessEffectId,
        int $armorBreakEffectId,
    ): void {
        $config = match ($variant) {
            0 => [0, null, 60, 0],
            1 => [4 + $tier * 2, $weaknessEffectId, 60 + $tier * 30, 2 + $tier],
            default => [8 + $tier * 3, $armorBreakEffectId, 120 + $tier * 30, 5 + $tier * 2],
        };

        DB::table('share_item_lock_configs')->updateOrInsert(
            ['share_item_id' => $chest->id],
            [
                'lock_required_skill' => $difficulty,
                'lock_duration_seconds' => 8 + $tier * 4 + $variant * 2,
                'experience_reward' => max(2, (int) ceil($difficulty / 10)),
                'trap_chance_penalty_percent' => $config[0],
                'trap_effect_id' => $config[1],
                'trap_effect_duration_seconds' => $config[2],
                'trap_damage_percent' => $config[3],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    private function syncLoot(ShareItem $chest, int $tier, int $variant): void
    {
        $lootIds = collect(self::LOOT_NAMES[$tier])
            ->mapWithKeys(fn (string $name): array => [$name => ShareItem::query()->where('name', $name)->value('id')]);
        if ($lootIds->contains(null)) {
            throw new RuntimeException("Не найдены предметы для содержимого сундука «{$chest->name}».");
        }

        DB::table('share_item_has_items')->where('parent_item_id', $chest->id)->delete();
        $materialCount = match ($variant) {
            0 => [1, 1],
            1 => [1, 2],
            default => [2, 3],
        };

        DB::table('share_item_has_items')->insert([
            [
                'parent_item_id' => $chest->id,
                'share_item_id' => $lootIds[self::LOOT_NAMES[$tier][0]],
                'min_count' => $materialCount[0],
                'max_count' => $materialCount[1],
                'drop_chance' => 100,
            ],
            [
                'parent_item_id' => $chest->id,
                'share_item_id' => $lootIds[self::LOOT_NAMES[$tier][1]],
                'min_count' => 1,
                'max_count' => $variant === 2 ? 2 : 1,
                'drop_chance' => [35, 55, 75][$variant],
            ],
            [
                'parent_item_id' => $chest->id,
                'share_item_id' => $lootIds[self::LOOT_NAMES[$tier][2]],
                'min_count' => 1,
                'max_count' => 1,
                'drop_chance' => [10, 15, 25][$variant],
            ],
        ]);
    }

    private function ensureLocationPlacement(ShareItem $chest): void
    {
        $exists = DB::table('item_on_locations')
            ->join('items', 'items.id', '=', 'item_on_locations.item_id')
            ->where('item_on_locations.location_id', self::LOCATION_ID)
            ->where('items.share_item_id', $chest->id)
            ->exists();
        if ($exists) {
            return;
        }

        $item = Item::query()->create(['share_item_id' => $chest->id]);
        DB::table('item_on_locations')->insert([
            'item_id' => $item->id,
            'location_id' => self::LOCATION_ID,
            'count' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function effectId(string $name): int
    {
        $id = DB::table('effects')->where('name', $name)->value('id');
        if ($id === null) {
            throw new RuntimeException("Не найден эффект ловушки «{$name}».");
        }

        return (int) $id;
    }
}
