<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Чинит ту же рассинхронизацию цен, что нашлась и была исправлена в Тир2/Тир3
 * (см. FixTier2Tier3ArmorPricingSeeder), теперь в Тир1 («Кожаный...», лвл 1-20):
 * `share_items.price` почти всей цепочки на заглушке `1`, `shop_items.price`
 * не пересчитывался под актуальную методику ценообразования.
 *
 * Дополнительно у «Кожаный щит» (id110) — та же дыра, что была у Тир3-брони:
 * `upgrade_to_share_item_id` = NULL, хотя «Усиленный кожаный щит»(407)/
 * «Закалённый кожаный щит»(456) существуют и связаны друг с другом — цепочка
 * не доходит до базового предмета. Self-material материал на материал id110
 * уже стоит (не трогаем), чинится только сама ссылка + цена.
 *
 * Цена = 150% от ЧИСТОГО дохода (running net worth, см. докблок
 * FixTier2Tier3ArmorPricingSeeder) — валовый доход между чекпоинтами слотов
 * минус цена уже купленной вещи на предыдущем чекпоинте. Лвл1 — спецкейс
 * (символические 10 монет на оба стартовых предмета, без множителя — доход
 * ещё практически нулевой, см. docs/economy-pricing-tier1-tier2.md), дальше
 * (2,4,7,10,13,16,20) — единая цепочка.
 *
 * Множитель редкости Тир1 (только 3 ступени, не 5): common×1.0 / uncommon×2.0 / rare×4.0.
 */
class FixTier1ArmorPricingSeeder extends Seeder
{
    private const RARITY_MULTIPLIER = [1.0, 2.0, 4.0];

    /** Базовая (common) цена на слот, вкл. параллельную линию оружия «Полуторный меч» (лвл10, = цена Поножей). */
    private const BASE_PRICES = [
        'Кожаные сапоги' => 105,
        'Кожаные наручи' => 668,
        'Кожаный шлем' => 2191,
        'Кожаные поножи' => 3769,
        'Полуторный меч' => 3769,
        'Кожаные наплечники' => 6014,
        'Кожаный щит' => 8591,
        'Кожаная кольчуга' => 18038,
    ];

    /** Лвл1 спецкейс — символическая цена, без множителя редкости (нет чейна). */
    private const LEVEL_1_ITEMS = [
        'Тесак Головореза' => 10,
        'Кожаный доспех' => 10,
    ];

    private const UPGRADE_GOLD_COST_RATE = 0.40;

    public function run(): void
    {
        // Чиним пропавшую ссылку в цепочке щита ДО того, как applyChain пойдёт по ней.
        $shieldBase = DB::table('share_items')->where('name', 'Кожаный щит')->first();
        $shieldRare1 = DB::table('share_items')->where('name', 'Усиленный кожаный щит')->first();
        if ($shieldBase !== null && $shieldRare1 !== null && $shieldBase->upgrade_to_share_item_id === null) {
            DB::table('share_items')->where('id', $shieldBase->id)->update([
                'upgrade_to_share_item_id' => $shieldRare1->id,
                'updated_at' => now(),
            ]);
            $this->command?->info('восстановлена ссылка: «Кожаный щит» -> «Усиленный кожаный щит»');
        }

        $itemsTouched = 0;
        $shopRowsTouched = 0;

        foreach (self::LEVEL_1_ITEMS as $name => $price) {
            $row = DB::table('share_items')->where('name', $name)->first();
            if ($row === null) {
                $this->command?->warn("не найден предмет: «{$name}»");

                continue;
            }
            DB::table('share_items')->where('id', $row->id)->update(['price' => $price, 'updated_at' => now()]);
            $shopItem = ShopItem::query()->where('share_item_id', $row->id)->first();
            if ($shopItem !== null) {
                $shopItem->price = $price;
                $shopItem->save();
                $shopRowsTouched++;
            }
            $itemsTouched++;
            $this->command?->info(sprintf('%s [common, lvl1]: price=%d%s', $row->name, $price, $shopItem !== null ? ' (+ shop_items синхронизирован)' : ''));
        }

        foreach (self::BASE_PRICES as $baseName => $basePrice) {
            $itemsTouched += $this->applyChain($baseName, $basePrice, $shopRowsTouched);
        }

        $this->command?->info("FixTier1ArmorPricingSeeder: обновлено предметов — {$itemsTouched}, строк shop_items — {$shopRowsTouched}");
    }

    private function applyChain(string $baseName, int $basePrice, int &$shopRowsTouched): int
    {
        $item = DB::table('share_items')->where('name', $baseName)->first();
        if ($item === null) {
            $this->command?->warn("не найден предмет: «{$baseName}»");

            return 0;
        }

        $chain = [$item];
        $current = $item;
        while ($current->upgrade_to_share_item_id !== null) {
            $next = DB::table('share_items')->where('id', $current->upgrade_to_share_item_id)->first();
            if ($next === null) {
                break;
            }
            $chain[] = $next;
            $current = $next;
        }

        $touched = 0;
        $chainLength = count($chain);

        foreach ($chain as $index => $row) {
            if ($index >= count(self::RARITY_MULTIPLIER)) {
                $this->command?->warn("«{$baseName}»: цепочка длиннее ожидаемой (шаг {$index}), пропускаю");

                continue;
            }

            $price = (int) round($basePrice * self::RARITY_MULTIPLIER[$index]);
            $isTerminal = $index === $chainLength - 1;
            $upgradeGoldCost = $isTerminal ? 0 : (int) round($price * self::UPGRADE_GOLD_COST_RATE);

            DB::table('share_items')->where('id', $row->id)->update([
                'price' => $price,
                'upgrade_gold_cost' => $upgradeGoldCost,
                'updated_at' => now(),
            ]);
            $touched++;

            $shopItem = ShopItem::query()->where('share_item_id', $row->id)->first();
            if ($shopItem !== null) {
                $shopItem->price = $price;
                $shopItem->save();
                $shopRowsTouched++;
            }

            $this->command?->info(sprintf(
                '%s [%s]: price=%d upgrade_gold_cost=%d%s',
                $row->name, $row->rarity, $price, $upgradeGoldCost,
                $shopItem !== null ? ' (+ shop_items синхронизирован)' : '',
            ));
        }

        return $touched;
    }
}
