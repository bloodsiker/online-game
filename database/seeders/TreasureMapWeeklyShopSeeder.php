<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class TreasureMapWeeklyShopSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $reputation = Reputation::findOrFail(35);
            if ($shop = Structure::where('reputation_id', $reputation->id)->first()) {
                $this->command?->info('Магазин уже перенесён в структуру #'.$shop->id.'.');

                return;
            }
            $map = ShareItem::findOrFail(1961);
            $category = $reputation->categories()->where('name->ru', 'Карты сокровищ')->first();
            if ($category === null) {
                // Replace only the empty placeholder created for this NPC, preserving its public URL.
                $placeholder = $reputation->categories()->whereKey(23)->first();
                if ($placeholder?->getTranslation('name', 'ru') === 'Товары кладоискателя'
                    && ! $reputation->shopItems()->where('share_structure_category_id', 23)->exists()
                    && DB::table('reputation_categories')->where('share_structure_category_id', 23)->count() === 1) {
                    $category = $placeholder;
                } else {
                    $category = new ShareStructureCategory;
                }
                $category->setTranslations('name', ['ru' => 'Карты сокровищ', 'uk' => 'Карти скарбів']);
                $category->is_active = true;
                $category->save();
                $reputation->categories()->syncWithoutDetaching([$category->id]);
            }
            $offer = ReputationShopItem::firstOrCreate(
                ['reputation_id' => $reputation->id, 'share_item_id' => $map->id],
                ['share_structure_category_id' => $category->id, 'price' => (int) $map->price, 'diamond' => 0,
                    'min_points' => 0, 'sort_order' => 0, 'purchase_limit_quantity' => 1, 'purchase_limit_period_seconds' => 7 * 86400],
            );
            $this->command?->info('Карты сокровищ: раздел #'.$category->id.', предложение #'.$offer->id.' (1 карта за 7 дней).');
        });
    }
}
