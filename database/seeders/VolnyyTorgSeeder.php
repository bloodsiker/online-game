<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Trade\Domain\Services\TradeBalance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VolnyyTorgSeeder extends Seeder
{
    public function run(): void
    {
        // Validate all locations before writing; never silently attach to another map.
        foreach ([2, 102, 1582] as $location) {
            if (! DB::table('locations')->where('id', $location)->exists()) {
                throw new RuntimeException('Нет локации '.$location.' для торговой точки.');
            }
        }
        DB::transaction(function (): void {
            $skill = DB::table('skills')->where('name', TradeBalance::SKILL_NAME)->first();
            $skillId = $skill?->id ?? DB::table('skills')->insertGetId([
                'name' => TradeBalance::SKILL_NAME, 'type' => 'peaceful',
                'description' => 'Перевозка товаров между торговцами: опыт за продажу, больше груза и прибыли с ростом навыка.',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            for ($level = 1; $level <= 300; $level++) {
                DB::table('skill_level_requirements')->insertOrIgnore([
                    'skill_id' => $skillId, 'lvl' => $level,
                    'exp_required' => TradeBalance::levelThreshold($level), 'exp_diff' => 100 + 5 * ($level - 1),
                ]);
            }
            $definitions = [
                ['Хельга Пряный Ряд', 'Нейрин', 2, 100, ['Крашеная холстина', 'Пряные красители', 'Серебряная тесьма', 'Шёлковые рулоны', 'Парча мастеров', 'Золотое шитьё']],
                ['Сборщица Ирма', 'Шепчущий Лес', 102, 300, ['Связка лесных мехов', 'Душистый сбор', 'Лесной янтарь', 'Редкие ароматические смолы', 'Королевский мех', 'Сердцевина древнего дерева']],
                ['Рудокоп Гуннар', 'Карригмор', 1582, 1500, ['Мешок рудного песка', 'Медные слитки', 'Самоцветная крошка', 'Серебряная жила', 'Северные самоцветы', 'Кристаллы глубин']],
            ];
            $posts = [];
            $prices = [40, 150, 500, 1500, 3000, 5000];
            $levels = [1, 50, 100, 150, 200, 250];
            foreach ($definitions as [$name, $city, $location, $license, $names]) {
                $npc = Npc::query()->firstOrCreate(['name' => $name], [
                    'location_id' => $location, 'is_active' => true, 'hide_location' => false,
                    'description' => 'Торговец Вольного торга. Покупает привезённый груз и продаёт местные товары.',
                ]);
                $postId = $this->create('trade_posts', ['npc_id' => $npc->id], ['name' => $city, 'license_price' => $license]);
                $goods = [];
                foreach ($names as $index => $itemName) {
                    $item = ShareItem::query()->where('type', ShareItemType::TRADE_GOODS->value)->where('name', $itemName)->first();
                    if (! $item) {
                        $item = new ShareItem;
                        $item->name = $itemName;
                        $item->type = ShareItemType::TRADE_GOODS;
                        $item->rarity = ItemRarity::COMMON;
                        $item->description = 'Торговый груз из '.$city.'. Можно продать другому торговцу Вольного торга при наличии его лицензии.';
                        $item->price = 0;
                        $item->save();
                    }
                    $good = $this->create('trade_goods', ['share_item_id' => $item->id], [
                        'trade_post_id' => $postId, 'base_price' => $prices[$index], 'required_skill_lvl' => $levels[$index], 'sort_order' => $index,
                    ]);
                    $this->create('trade_post_stocks', ['trade_good_id' => $good], [
                        'current_quantity' => 15, 'max_quantity' => 15, 'last_restock_at' => now(),
                    ]);
                    $goods[] = $good;
                }
                $posts[] = ['id' => $postId, 'goods' => $goods];
            }
            $offers = ['near' => [42, 158, 525, 1575, 3150, 5250], 'mid' => [43, 162, 538, 1613, 3225, 5375], 'far' => [44, 165, 550, 1650, 3300, 5500]];
            foreach ([[0, 1, 'near', 5], [1, 2, 'mid', 12], [0, 2, 'far', 25]] as [$a,$b,$distance,$xp]) {
                $this->create('trade_routes', ['trade_post_a_id' => min($posts[$a]['id'], $posts[$b]['id']), 'trade_post_b_id' => max($posts[$a]['id'], $posts[$b]['id'])], ['distance_tier' => $distance, 'exp_per_unit' => $xp]);
                foreach ([[$a, $b], [$b, $a]] as [$source,$target]) {
                    foreach ($posts[$source]['goods'] as $tier => $good) {
                        $this->create('trade_buy_offers', ['trade_post_id' => $posts[$target]['id'], 'trade_good_id' => $good], ['buy_price' => $offers[$distance][$tier]]);
                    }
                }
            }
        });
    }

    private function create(string $table, array $key, array $values): int
    {
        $existing = DB::table($table)->where($key)->value('id');

        return $existing ? (int) $existing : DB::table($table)->insertGetId($key + $values + ['created_at' => now(), 'updated_at' => now()]);
    }
}
