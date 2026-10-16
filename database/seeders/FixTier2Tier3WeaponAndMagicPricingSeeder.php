<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedItemSeederQuery;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Database\Seeder;

/**
 * Дополняет FixTier2Tier3ArmorPricingSeeder (который чинил только броню):
 * оружие Тир2/Тир3 и 2 магических сета Тир3 («Архимаг»/«Астрал») — та же
 * `price=1` рассинхронизация, но эти предметы вообще не были в прошлой
 * правке.
 *
 * Оружие и щит — САМОСТОЯТЕЛЬНЫЕ (не цепные) покупки относительно брони:
 * Тир2 оружие разлокается на том же lvl20, что и Нагрудник — нельзя
 * посчитать «доход между двумя покупками одного уровня» (его физически
 * нет), поэтому цена = 150% валового дохода к lvl20 (тому же, что у
 * Нагрудника) ×1.05 — оружие чуть дороже брони того же уровня, как и было
 * задумано изначально. Тир3 оружие разлокается РАНЬШЕ первой брони (lvl55
 * против lvl58 щита) — если бы оно было первым звеном общей цепи, вся
 * броня дальше стала бы ещё дешевле (готовое к покупке оружие «съедает»
 * общий доход) — решили не цеплять: оружие и щит остаются независимыми
 * тратами, как и было при первой сборке методики (см. docs/economy-pricing-tier1-tier2.md).
 *
 * Магические сеты «Архимаг»/«Астрал» используют ТУ ЖЕ чекпоинт-таблицу, что
 * «Титан» (общая экономика Тир3, см. docs/economy-pricing-tier3.md) — только
 * архетипный сдвиг ±1.5% и нет слота щита.
 */
class FixTier2Tier3WeaponAndMagicPricingSeeder extends Seeder
{
    private const TIER2_RARITY_MULTIPLIER = [1.0, 1.6, 2.2, 3.0, 4.2];

    private const TIER3_RARITY_MULTIPLIER = [1.0, 1.375, 1.875, 2.625];

    private const UPGRADE_GOLD_COST_RATE = 0.40;

    /** Тир2 оружие: база = 150% дохода к lvl20 (=цена «Нагрудник «Мамонт»») × 1.05. */
    private const TIER2_WEAPONS = [
        'Кастет «Мамонт»' => 6649,           // без цепочки редкости
        'Сумеречный Дайто' => 6549,          // -1.5% от базы, есть цепочка
        'Топор палача' => 6749,              // +1.5% от базы, есть цепочка (Family B ×1.15/шаг)
        'Меч палача' => 3375,                // = Топор палача × 0.5, есть цепочка (Family B)
    ];

    /** Тир3 оружие «Титан»: база = 150% дохода к lvl55 (см. докблок). */
    private const TIER3_WEAPON_TITAN_BASE = 1669421;

    /** Тир3 щит (Титан-only): база = 150% дохода к lvl58 (см. докблок), самостоятельная покупка. */
    private const TIER3_SHIELD_PRICE = 2781905;

    /** Тир3, базовая (uncommon) цена «Архимаг»(-1.5%)/«Астрал»(+1.5%) на слот — та же таблица, что «Титан». */
    private const MAGIC_ARMOR = [
        'Мантия' => 3417826,
        'Сапоги' => 1024217,
        'Наручи' => 3245396,
        'Капюшон' => 3528961,
        'Поножи' => 5284679,
        'Наплечники' => 6988859,
        'Плащ' => 9634118,
    ];

    private const MAGIC_WEAPON_BASE = 1669421;

    public function run(): void
    {
        $itemsTouched = 0;
        $shopRowsTouched = 0;

        // --- Тир2: оружие ---
        $itemsTouched += $this->setPriceOnly('Кастет «Мамонт»', self::TIER2_WEAPONS['Кастет «Мамонт»'], $shopRowsTouched);
        $itemsTouched += $this->applyChain('Сумеречный Дайто', self::TIER2_WEAPONS['Сумеречный Дайто'], self::TIER2_RARITY_MULTIPLIER, $shopRowsTouched);
        $itemsTouched += $this->applyChain('Топор палача', self::TIER2_WEAPONS['Топор палача'], self::palachMultiplier(), $shopRowsTouched);
        $itemsTouched += $this->applyChain('Меч палача', self::TIER2_WEAPONS['Меч палача'], self::palachMultiplier(), $shopRowsTouched);

        // --- Тир3: оружие (Титан=база, Призрачный=-1.5%, Жнец=+1.5%; у Жнеца/Призрачного по 2-3 роли, тот же прайс) ---
        $t3WeaponTitan = self::TIER3_WEAPON_TITAN_BASE;
        $t3WeaponGhostReaper = (int) round($t3WeaponTitan * 0.985);
        $t3WeaponReaper = (int) round($t3WeaponTitan * 1.015);
        $itemsTouched += $this->applyChain('Булава «Титан»', $t3WeaponTitan, self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched);
        $itemsTouched += $this->applyChain('Молот «Титан»', (int) round($t3WeaponTitan * 1.5), self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched);
        foreach (['Призрачный клинок' => $t3WeaponGhostReaper, 'Призрачный лук' => (int) round($t3WeaponGhostReaper * 1.5), 'Призрачный кнут' => $t3WeaponGhostReaper] as $name => $price) {
            $itemsTouched += $this->applyChain($name, $price, self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched);
        }
        foreach (['Топор жнеца' => $t3WeaponReaper, 'Топор двуручный жнеца' => (int) round($t3WeaponReaper * 1.5), 'Меч жнеца' => (int) round($t3WeaponReaper * 0.75)] as $name => $price) {
            $itemsTouched += $this->applyChain($name, $price, self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched);
        }

        // --- Тир3: щит (самостоятельная покупка, только Титан) ---
        $itemsTouched += $this->applyChain('Щит «Титан»', self::TIER3_SHIELD_PRICE, self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched);

        // --- Магические сеты ---
        foreach (self::MAGIC_ARMOR as $slot => $base) {
            $archimagName = $slot === 'Мантия' ? 'Мантия «Архимаг»' : ($slot.' «Архимаг»');
            $astralName = $slot === 'Мантия' ? 'Мантия «Астрал»' : ($slot.' «Астрал»');
            $itemsTouched += $this->applyChain($archimagName, (int) round($base * 0.985), self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched);
            $itemsTouched += $this->applyChain($astralName, (int) round($base * 1.015), self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched);
        }

        $archimagWeaponPrice = (int) round(self::MAGIC_WEAPON_BASE * 0.985);
        $astralWeaponPrice = (int) round(self::MAGIC_WEAPON_BASE * 1.015);
        foreach (['Фолиант', 'Посох', 'Сфера'] as $weaponType) {
            $itemsTouched += $this->applyChain($weaponType.' «Архимаг»', $archimagWeaponPrice, self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched);
            $itemsTouched += $this->applyChain($weaponType.' «Астрал»', $astralWeaponPrice, self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched);
        }

        $this->command?->info("FixTier2Tier3WeaponAndMagicPricingSeeder: обновлено предметов — {$itemsTouched}, строк shop_items — {$shopRowsTouched}");
    }

    /** @return array<int, float> Family B (Палач) — ×1.15/шаг. */
    private static function palachMultiplier(): array
    {
        return [1.0, 1.15, 1.3225, 1.520875, 1.74900625];
    }

    private function setPriceOnly(string $name, int $price, int &$shopRowsTouched): int
    {
        $row = LocalizedItemSeederQuery::items()->where('name', $name)->first();
        if ($row === null) {
            $this->command?->warn("не найден предмет: «{$name}»");

            return 0;
        }
        LocalizedItemSeederQuery::items()->where('id', $row->id)->update(['price' => $price, 'updated_at' => now()]);
        $shopItem = ShopItem::query()->where('share_item_id', $row->id)->first();
        if ($shopItem !== null) {
            $shopItem->price = $price;
            $shopItem->save();
            $shopRowsTouched++;
        }
        $this->command?->info(sprintf('%s [%s, без цепочки]: price=%d%s', $row->name, $row->rarity, $price, $shopItem !== null ? ' (+ shop_items синхронизирован)' : ''));

        return 1;
    }

    /** @param  array<int, float>  $rarityMultiplier */
    private function applyChain(string $baseName, int $basePrice, array $rarityMultiplier, int &$shopRowsTouched): int
    {
        $item = LocalizedItemSeederQuery::items()->where('name', $baseName)->first();
        if ($item === null) {
            $this->command?->warn("не найден предмет: «{$baseName}»");

            return 0;
        }

        $chain = [$item];
        $current = $item;
        while ($current->upgrade_to_share_item_id !== null) {
            $next = LocalizedItemSeederQuery::items()->where('id', $current->upgrade_to_share_item_id)->first();
            if ($next === null) {
                break;
            }
            $chain[] = $next;
            $current = $next;
        }

        $touched = 0;
        $chainLength = count($chain);

        foreach ($chain as $index => $row) {
            if ($index >= count($rarityMultiplier)) {
                $this->command?->warn("«{$baseName}»: цепочка длиннее ожидаемой (шаг {$index}), пропускаю");

                continue;
            }

            $price = (int) round($basePrice * $rarityMultiplier[$index]);
            $isTerminal = $index === $chainLength - 1;
            $upgradeGoldCost = $isTerminal ? 0 : (int) round($price * self::UPGRADE_GOLD_COST_RATE);

            LocalizedItemSeederQuery::items()->where('id', $row->id)->update([
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
