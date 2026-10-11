<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Чинит рассинхронизацию цен брони Тир2 (Мамонт/Сумеречный/Палач) и Тир3
 * (Титан/Призрачный/Жнец), обнаруженную после аварии БД 25 сентября 2026.
 *
 * Найдено при разборе (запрос пользователя «монет мало» → «нет дефицита при
 * покупке шмота»): `share_items.price` почти всей цепочки редкости стоял на
 * заглушке `1` (не соответствует НИ старым, НИ новым числам из
 * docs/economy-pricing-tier1-tier2.md и -tier3.md), а `shop_items.price`
 * (то, что РЕАЛЬНО списывает магазин) у Тир2 откатился на дореформенные
 * числа («было» из документа), у части Тир3-брони — на ещё более старую
 * формулу `HighTierEquipmentSeeder::price() = 80×level^1.5`. Из-за этого
 * шмотка на разлоке стоила в разы дешевле реального фарма — заложенный
 * дефицит ~33% (цена = 150% от накопленного золота тира) не работал.
 *
 * v3 (по прямому указанию пользователя, 2026-09-27): цена слота = 150% от
 * ЧИСТОГО дохода — валовый доход, заработанный МЕЖДУ предыдущим и текущим
 * чекпоинтом слота, минус ничего не восстанавливается («вычитаются вещи,
 * купленные на предыдущих уровнях») — т.е. это running-ledger модель:
 * netWorth += income(prevCheckpoint+1..thisCheckpoint); price = netWorth×1.5;
 * netWorth -= price (уходит в минус, как и должно — дефицит подтверждён
 * симуляцией: netWorth остаётся отрицательным на каждом шаге, ~33% от цены
 * купленной вещи, не растёт безгранично). Раньше (v2) цена слота считалась
 * от ВАЛОВОГО дохода с начала тира целиком, без учёта того, что часть уже
 * потрачена на более ранние слоты — из-за этого поздние слоты (Кольчуга
 * «Титан» и т.п.) были завышены в разы (лвл90: 45.1М → 9.6М после v3).
 *
 * Множитель цепочки редкости не меняется (Family A ×[1,1.6,2.2,3,4.2],
 * Family B ×1.15/шаг, Тир3 ×[1,1.375,1.875,2.625] от uncommon) — меняется
 * только базовая (common/uncommon) цена на слот.
 *
 * Только БРОНЯ (7 слотов + щит/кольчуга-финал на каждую из 6 линеек) —
 * оружие («Кастет «Мамонт»»/«Топор палача»/«Булава «Титан»» и т.д.) сюда
 * намеренно не включено, отдельная задача.
 */
class FixTier2Tier3ArmorPricingSeeder extends Seeder
{
    /** Family A (Мамонт=1.0, Сумеречный=0.985) — общий множитель редкости [common,uncommon,rare,epic,legendary]. */
    private const FAMILY_A_RARITY_MULTIPLIER = [1.0, 1.6, 2.2, 3.0, 4.2];

    /** Family B (Палач) — общий множитель редкости ×1.15/шаг. */
    private const FAMILY_B_RARITY_MULTIPLIER = [1.0, 1.15, 1.3225, 1.520875, 1.74900625];

    /** Тир3 — общий множитель редкости от uncommon (там нет common-тира). */
    private const TIER3_RARITY_MULTIPLIER = [1.0, 1.375, 1.875, 2.625];

    /**
     * Тир2, базовая (common) цена «Мамонт»/«Сумеречный» на слот — из
     * docs/economy-pricing-tier1-tier2.md, таблица «Итоговые цены».
     *
     * @var array<string, array{0: string, 1: int, 2: int}> имя слота Мамонт => [имя слота Сумеречный, цена Мамонт, цена Сумеречный]
     */
    private const TIER2_FAMILY_A = [
        'Нагрудник «Мамонт»' => ['Сумеречная броня', 6332, 6237],
        'Сапоги «Мамонт»' => ['Сумеречные сапоги', 61049, 60133],
        'Рукавицы «Мамонт»' => ['Сумеречные перчатки', 86209, 84916],
        'Шлем «Мамонт»' => ['Сумеречная маска', 158548, 156170],
        'Поножи «Мамонт»' => ['Сумеречные поножи', 269030, 264995],
        'Наплечники «Мамонт»' => ['Сумеречные наплечники', 358294, 352920],
        'Кольчуга «Мамонт»' => ['Сумеречная кольчуга', 945094, 930918],
        'Щит «Мамонт»' => [null, 513233, null],
    ];

    /** Тир2, Family B (Палач) — базовая (common) цена на слот. */
    private const TIER2_FAMILY_B = [
        'Жилет палача' => 6427,
        'Сапоги палача' => 61965,
        'Наручи палача' => 87502,
        'Маска палача' => 160926,
        'Поножи палача' => 273065,
        'Наплечники палача' => 363668,
        'Рубаха палача' => 959270,
    ];

    /**
     * Тир3, базовая (uncommon) цена «Титан»/«Призрачный»(-1.5%)/«Жнец»(+1.5%) на слот —
     * пересчитано по прямому указанию пользователя: цена = 150% от ЧИСТОГО дохода
     * (running net worth — валовый доход МИНУС всё, что уже потрачено на броню
     * предыдущих чекпоинтов того же тира), а не от валового накопленного дохода
     * с начала тира целиком. Это даёт компаундирующийся, но ограниченный дефицит
     * (~33% от каждой покупки) вместо цен, растущих на полную сумму ВСЕГО дохода
     * с начала тира без учёта уже потраченного.
     *
     * @var array<string, array{0: string|null, 1: string|null, 2: int, 3: ?int, 4: ?int}>
     */
    private const TIER3_ARMOR = [
        // Щит и оружие — самостоятельные (не цепные) покупки, см. FixTier2Tier3WeaponAndMagicPricingSeeder.
        'Щит «Титан»' => [null, null, 2781905, null, null],
        'Нагрудник «Титан»' => ['Призрачная броня', 'Жилет жнеца', 3417826, 3366559, 3469093],
        'Сапоги «Титан»' => ['Призрачные сапоги', 'Сапоги жнеца', 1024217, 1008854, 1039580],
        'Наручи «Титан»' => ['Призрачные перчатки', 'Наручи жнеца', 3245396, 3196715, 3294077],
        'Шлем «Титан»' => ['Призрачная маска', 'Маска жнеца', 3528961, 3476027, 3581895],
        'Поножи «Титан»' => ['Призрачные поножи', 'Поножи жнеца', 5284679, 5205409, 5363949],
        'Наплечники «Титан»' => ['Призрачные наплечники', 'Наплечники жнеца', 6988859, 6884026, 7093692],
        'Кольчуга «Титан»' => ['Призрачная кольчуга', 'Рубаха жнеца', 9634118, 9489606, 9778630],
    ];

    /** Доля золота текущего тира редкости на апгрейд — актуальная (смягчённая) политика, см. docs. */
    private const UPGRADE_GOLD_COST_RATE = 0.40;

    public function run(): void
    {
        $itemsTouched = 0;
        $shopRowsTouched = 0;

        foreach (self::TIER2_FAMILY_A as $mammothName => [$twilightName, $mammothBase, $twilightBase]) {
            $itemsTouched += $this->applyChain($mammothName, $mammothBase, self::FAMILY_A_RARITY_MULTIPLIER, $shopRowsTouched);
            if ($twilightName !== null) {
                $itemsTouched += $this->applyChain($twilightName, $twilightBase, self::FAMILY_A_RARITY_MULTIPLIER, $shopRowsTouched);
            }
        }

        foreach (self::TIER2_FAMILY_B as $name => $base) {
            $itemsTouched += $this->applyChain($name, $base, self::FAMILY_B_RARITY_MULTIPLIER, $shopRowsTouched);
        }

        foreach (self::TIER3_ARMOR as $titanName => [$ghostName, $reaperName, $titanBase, $ghostBase, $reaperBase]) {
            $itemsTouched += $this->applyChain($titanName, $titanBase, self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched, isTier3: true);
            if ($ghostName !== null) {
                $itemsTouched += $this->applyChain($ghostName, $ghostBase, self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched, isTier3: true);
            }
            if ($reaperName !== null) {
                $itemsTouched += $this->applyChain($reaperName, $reaperBase, self::TIER3_RARITY_MULTIPLIER, $shopRowsTouched, isTier3: true);
            }
        }

        $this->command?->info("FixTier2Tier3ArmorPricingSeeder: обновлено предметов — {$itemsTouched}, строк shop_items — {$shopRowsTouched}");
    }

    /** @param array<int, float> $rarityMultiplier */
    private function applyChain(string $baseName, int $basePrice, array $rarityMultiplier, int &$shopRowsTouched, bool $isTier3 = false): int
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
            if ($index >= count($rarityMultiplier)) {
                $this->command?->warn("«{$baseName}»: цепочка длиннее ожидаемой (шаг {$index}), пропускаю");

                continue;
            }

            $price = (int) round($basePrice * $rarityMultiplier[$index]);
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
