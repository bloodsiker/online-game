<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstantMoneyRewardSeeder extends Seeder
{
    private const NAME = 'Горстка монет';

    /** @var array<string, array{0: int, 1: int}> */
    private const AMOUNTS_BY_RARITY = [
        'common' => [50, 150],
        'uncommon' => [150, 400],
        'rare' => [400, 1_000],
        'epic' => [1_000, 2_500],
        'legendary' => [2_500, 6_000],
        'heroic' => [6_000, 15_000],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $rewardId = $this->upsertReward();

            DB::table('share_item_instant_rewards')->updateOrInsert(
                ['share_item_id' => $rewardId],
                [
                    'reward_type' => 'money',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            DB::table('share_items')
                ->where('type', 'chest')
                ->get(['id', 'rarity'])
                ->each(function (object $container) use ($rewardId): void {
                    [$minAmount, $maxAmount] = self::AMOUNTS_BY_RARITY[$container->rarity]
                        ?? self::AMOUNTS_BY_RARITY['common'];

                    DB::table('share_item_has_items')->updateOrInsert(
                        [
                            'parent_item_id' => $container->id,
                            'share_item_id' => $rewardId,
                        ],
                        [
                            'min_count' => $minAmount,
                            'max_count' => $maxAmount,
                            'drop_chance' => 65,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                });
        });

        $this->command?->info('Создана мгновенная награда «Горстка монет» и добавлена в содержимое сундуков.');
    }

    private function upsertReward(): int
    {
        $existing = DB::table('share_items')->where('name', self::NAME)->first();
        $attributes = [
            'description' => 'Найденные монеты сразу зачисляются на баланс персонажа и не занимают место в рюкзаке.',
            'type' => 'misc',
            'rarity' => 'common',
            'image' => '/img/bank_stock/new_coins.gif',
            'is_active' => true,
            'is_sell' => false,
            'is_auction_sellable' => false,
            'is_give' => false,
            'is_clan_warehouse_allowed' => false,
            'is_droppable' => false,
            'is_stackable' => false,
            'is_slot_usable' => false,
            'is_use' => false,
            'is_weight' => false,
            'price' => 0,
            'updated_at' => now(),
        ];

        if ($existing === null) {
            return (int) DB::table('share_items')->insertGetId([
                'name' => self::NAME,
                ...$attributes,
                'created_at' => now(),
            ]);
        }

        DB::table('share_items')->where('id', $existing->id)->update($attributes);

        return (int) $existing->id;
    }
}
