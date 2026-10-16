<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedItemSeederQuery;
use Illuminate\Database\Seeder;

/**
 * Кастет «Титан» (все 4 тира, включая только что достроенный uncommon
 * id=1827) имел price=1 на всех тирах — явный плейсхолдер (ни один
 * другой предмет в игре не стоит 1 золотую монету). По прямому указанию
 * пользователя выставляет реальную цену.
 *
 * Значения взяты 1:1 у «Булава «Титан»» (1385-1388) — того же слота/тира
 * того же сета: кастет уже полностью повторяет стат-профиль булавы на
 * каждом тире (см. TitanKnuckleUncommonTierSeeder), а коэффициенты
 * прогрессии цены булавы (1.375 / 1.364 / 1.400) совпадают с уже
 * существующими коэффициентами прогрессии break_crystal/upgrade_gold_cost
 * у самого кастета — значит цена булавы и есть «та самая», которую
 * кастету забыли проставить.
 */
class TitanKnucklePriceFixSeeder extends Seeder
{
    /** @var array<int, int> share_item_id => price (скопировано с Булава «Титан» 1385-1388) */
    private const PRICES = [
        1827 => 1669421, // Кастет «Титан» (uncommon)   = Булава «Титан» (1385)
        1302 => 2295454, // Закалённый кастет (rare)     = Закалённая булава (1386)
        1303 => 3130164, // Заговорённый кастет (epic)   = Заговорённая булава (1387)
        1304 => 4382230, // Мифический кастет (legendary)= Мифическая булава (1388)
    ];

    public function run(): void
    {
        $updated = 0;
        foreach (self::PRICES as $itemId => $price) {
            $updated += LocalizedItemSeederQuery::items()->where('id', $itemId)->update([
                'price' => $price,
                'updated_at' => now(),
            ]);
        }

        $this->command?->info("TitanKnucklePriceFixSeeder: цена проставлена на {$updated} тирах кастета «Титан»");
    }
}
