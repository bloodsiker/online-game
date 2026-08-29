<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PeacefulProfessionDemoSeeder extends Seeder
{
    private const MAP_NAME = 'Шепчущий Лес';

    private const CONTENT = [
        [
            'profession' => 'Травник',
            'tool' => 'Серп',
            'tool_description' => 'Простой бесконечный инструмент для сбора трав.',
            'tool_image' => '/img/resource/gathering/sickle.svg',
            'resource' => 'Лечебная трава',
            'resource_description' => 'Лесное растение, пригодное для ремесла.',
            'resource_image' => '/img/resource/gathering/herb.svg',
            'gathering_time' => 5,
            'respawn_time' => 30,
            'experience' => 3,
        ],
        [
            'profession' => 'Рыбак',
            'tool' => 'Удочка',
            'tool_description' => 'Простой бесконечный инструмент для рыбалки.',
            'tool_image' => '/img/resource/gathering/fishing-rod.svg',
            'resource' => 'Речной окунь',
            'resource_description' => 'Обычная рыба из лесных водоёмов.',
            'resource_image' => '/img/resource/gathering/fish.svg',
            'gathering_time' => 5,
            'respawn_time' => 30,
            'experience' => 3,
        ],
        [
            'profession' => 'Геолог',
            'tool' => 'Кирка',
            'tool_description' => 'Простой бесконечный инструмент для добычи руды.',
            'tool_image' => '/img/resource/gathering/pickaxe.svg',
            'resource' => 'Медная руда',
            'resource_description' => 'Кусок медной руды из лесных выходов породы.',
            'resource_image' => '/img/resource/gathering/ore.svg',
            'gathering_time' => 5,
            'respawn_time' => 30,
            'experience' => 3,
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $mapId = DB::table('maps')->where('name', self::MAP_NAME)->value('id');
            if ($mapId === null) {
                throw new RuntimeException('Карта «'.self::MAP_NAME.'» не найдена.');
            }

            $userId = DB::table('players')->orderBy('id')->value('user_id');
            if ($userId === null) {
                throw new RuntimeException('Не найден игрок, которому можно выдать тестовые инструменты.');
            }

            foreach (self::CONTENT as $entry) {
                $skillId = DB::table('skills')
                    ->where('name', $entry['profession'])
                    ->where('type', 'peaceful')
                    ->value('id');

                if ($skillId === null) {
                    throw new RuntimeException('Мирная профессия «'.$entry['profession'].'» не найдена.');
                }

                $toolId = $this->upsertShareItem($entry['tool'], [
                    'type' => 'tool',
                    'description' => $entry['tool_description'],
                    'image' => $entry['tool_image'],
                    'is_two_hand' => false,
                    'count_use' => 0,
                    'is_active' => true,
                    'is_sell' => false,
                    'is_give' => true,
                    'is_droppable' => true,
                    'is_stackable' => false,
                    'is_slot_usable' => false,
                    'is_weight' => true,
                    'price' => 0,
                    'break_crystal' => 0,
                    'slot' => 'hand',
                    'skill_id' => null,
                    'skill_lvl' => null,
                    'skill_exp' => null,
                    'gathering_time_seconds' => null,
                    'gathering_respawn_seconds' => null,
                    'gathering_tool_share_item_id' => null,
                    'rarity' => 'common',
                ]);

                $resourceId = $this->upsertShareItem($entry['resource'], [
                    'type' => 'resource',
                    'description' => $entry['resource_description'],
                    'image' => $entry['resource_image'],
                    'is_two_hand' => false,
                    'count_use' => 0,
                    'is_active' => true,
                    'is_sell' => true,
                    'is_give' => true,
                    'is_droppable' => true,
                    'is_stackable' => true,
                    'is_slot_usable' => false,
                    'is_weight' => true,
                    'price' => 0,
                    'break_crystal' => 0,
                    'slot' => null,
                    'skill_id' => $skillId,
                    'skill_lvl' => 1,
                    'skill_exp' => $entry['experience'],
                    'gathering_time_seconds' => $entry['gathering_time'],
                    'gathering_respawn_seconds' => $entry['respawn_time'],
                    'gathering_tool_share_item_id' => $toolId,
                    'rarity' => 'common',
                ]);

                DB::table('map_gathering_resources')->updateOrInsert(
                    ['map_id' => $mapId, 'share_item_id' => $resourceId],
                    [
                        'max_active' => 1,
                        'min_x' => 8,
                        'max_x' => 92,
                        'min_y' => 8,
                        'max_y' => 92,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );

                $this->giveTool((int) $userId, $toolId);
            }
        });

        $this->command?->info('Тестовые ресурсы добавлены на карту «'.self::MAP_NAME.'», инструменты выданы первому игроку.');
    }

    private function upsertShareItem(string $name, array $attributes): int
    {
        $id = DB::table('share_items')->where('name', $name)->value('id');
        $now = now();

        if ($id === null) {
            return (int) DB::table('share_items')->insertGetId([
                'name' => $name,
                ...$attributes,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('share_items')->where('id', $id)->update([
            ...$attributes,
            'updated_at' => $now,
        ]);

        return (int) $id;
    }

    private function giveTool(int $userId, int $shareItemId): void
    {
        $alreadyOwned = DB::table('backpacks')
            ->join('items', 'items.id', '=', 'backpacks.item_id')
            ->where('backpacks.user_id', $userId)
            ->where('items.share_item_id', $shareItemId)
            ->exists();

        if ($alreadyOwned) {
            return;
        }

        $now = now();
        $itemId = DB::table('items')->insertGetId([
            'share_item_id' => $shareItemId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('backpacks')->insert([
            'user_id' => $userId,
            'item_id' => $itemId,
            'equipped' => false,
            'count' => 1,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
