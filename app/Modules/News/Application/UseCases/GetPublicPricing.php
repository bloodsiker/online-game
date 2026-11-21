<?php

declare(strict_types=1);

namespace App\Modules\News\Application\UseCases;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Публичный прайс (/pricing) для гостей и модерации платёжных систем:
 * курс бриллиантов, пакеты, премиум-статусы и товары премиум-магазина — из тех же таблиц, что и игра.
 */
final class GetPublicPricing
{
    private const PREMIUM_SHOP_ID = 10;

    private const PACKS = [30, 50, 100, 200, 500, 1000];

    /** @return array{priceMinor: int, minimumMinor: int, currency: string, packs: list<array{diamonds: int, amount: string}>, premiumLevels: Collection<int, object>, categories: Collection<int, object>, goldPerDiamond: int} */
    public function execute(): array
    {
        $settings = DB::table('payment_settings')->find(1);
        $price = (int) ($settings->diamond_price_minor ?? 0);
        $minimum = (int) ($settings->minimum_amount_minor ?? 0);
        $minDiamonds = $price > 0 ? (int) ceil($minimum / $price) : 0;

        $packs = [];
        foreach (self::PACKS as $diamonds) {
            if ($price > 0 && $diamonds >= $minDiamonds) {
                $packs[] = ['diamonds' => $diamonds, 'amount' => number_format($diamonds * $price / 100, 2, '.', ' ')];
            }
        }

        $locale = app()->getLocale() === 'uk' ? 'uk' : 'ru';
        $localized = function (?string $json) use ($locale): string {
            $name = json_decode((string) $json, true);

            return is_array($name) ? (string) ($name[$locale] ?? $name['ru'] ?? reset($name)) : (string) $json;
        };

        // Порядок категорий и товаров — как в игровом премиум-магазине (Structure::categories(), GetShopItems).
        $items = DB::table('shop_items')
            ->join('share_items', 'share_items.id', '=', 'shop_items.share_item_id')
            ->where('shop_items.structure_id', self::PREMIUM_SHOP_ID)
            ->where('shop_items.diamond', '>', 0)
            ->orderBy('shop_items.sort_order', 'desc')->orderBy('shop_items.id')
            ->get(['share_items.id', 'share_items.name', 'shop_items.diamond', 'shop_items.share_structure_category_id as category_id'])
            ->map(fn (object $row): object => (object) [
                'id' => (int) $row->id,
                'name' => $localized($row->name),
                'diamonds' => (int) $row->diamond,
                'categoryId' => (int) $row->category_id,
            ])
            ->groupBy('categoryId');

        $categories = DB::table('shop_categories')
            ->join('share_structure_categories', 'share_structure_categories.id', '=', 'shop_categories.share_structure_category_id')
            ->where('shop_categories.structure_id', self::PREMIUM_SHOP_ID)
            ->where('share_structure_categories.is_active', true)
            ->orderBy('shop_categories.id')
            ->get(['share_structure_categories.id', 'share_structure_categories.name'])
            ->filter(fn (object $category): bool => $items->has((int) $category->id))
            ->map(fn (object $category): object => (object) [
                'name' => $localized($category->name),
                'items' => $items->get((int) $category->id),
            ])
            ->values();

        $premiumLevels = DB::table('premium_levels')->whereNotNull('required_diamond_cents')->orderBy('level')->get()
            ->map(function (object $level): object {
                $bonuses = json_decode((string) $level->bonuses, true) ?: [];
                $enabled = $bonuses['_enabled'] ?? [];
                $on = fn (string $key): bool => (bool) ($enabled[$key] ?? false) && ! empty($bonuses[$key]);

                return (object) [
                    'level' => (int) $level->level,
                    'diamonds' => (int) round($level->required_diamond_cents / 100),
                    'experience' => $on('experience_percent') ? (int) $bonuses['experience_percent'] : 0,
                    'money' => $on('monster_money_percent') ? (int) $bonuses['monster_money_percent'] : 0,
                    'bagSlots' => $on('bag_slots') ? (int) $bonuses['bag_slots'] : 0,
                    'gathering' => $on('gathering_speed_percent') ? (int) $bonuses['gathering_speed_percent'] : 0,
                    'autoLoot' => $on('auto_loot'),
                    'anonymousAuction' => $on('anonymous_auction'),
                    'anonymousExchange' => $on('anonymous_exchange'),
                ];
            });

        return [
            'priceMinor' => $price, 'minimumMinor' => $minimum, 'currency' => (string) ($settings->currency ?? 'USD'),
            'packs' => $packs, 'premiumLevels' => $premiumLevels, 'categories' => $categories,
            'goldPerDiamond' => (int) DB::table('premium_settings')->where('id', 1)->value('gold_per_diamond'),
        ];
    }
}
