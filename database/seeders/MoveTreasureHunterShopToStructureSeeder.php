<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopCart;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopCart;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MoveTreasureHunterShopToStructureSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            // Purchases lock users first too: no old checkout can finish halfway through the move.
            User::without('player')->orderBy('id')->lockForUpdate()->get(['id']);
            $reputation = Reputation::with(['npc', 'categories', 'shopItems.requirements'])->findOrFail(35);
            $shop = Structure::where('reputation_id', $reputation->id)->first();
            if ($shop === null) {
                $shop = (new Structure)->forceFill([
                    'type' => Structure::TYPE_SHOP, 'reputation_id' => $reputation->id,
                    'npc_id' => $reputation->npc_id, 'location_id' => $reputation->npc->location_id,
                    'name' => ['ru' => 'Магазин кладоискателей', 'uk' => 'Магазин шукачів скарбів'],
                    'description' => ['ru' => '<p>Карты и припасы для искателей сокровищ. Некоторые товары доступны в ограниченном количестве на игрока.</p>', 'uk' => '<p>Карти та припаси для шукачів скарбів. Деякі товари доступні в обмеженій кількості на гравця.</p>'],
                ]);
                foreach (['name', 'description'] as $field) {
                    if (Schema::hasColumn('structures', $field.'_legacy')) {
                        $shop->setAttribute($field.'_legacy', $shop->getTranslation($field, 'ru'));
                    }
                }
                $shop->save();
            }
            $shop->categories()->syncWithoutDetaching($reputation->categories->modelKeys());
            foreach ($reputation->shopItems as $old) {
                $offer = ShopItem::firstOrCreate(
                    ['structure_id' => $shop->id, 'share_item_id' => $old->share_item_id],
                    ['share_structure_category_id' => $old->share_structure_category_id,
                        'price' => $old->price, 'diamond' => $old->diamond, 'sort_order' => $old->sort_order,
                        'min_reputation_points' => $old->min_points,
                        'purchase_limit_quantity' => $old->purchase_limit_quantity,
                        'purchase_limit_period_seconds' => $old->purchase_limit_period_seconds],
                );
                foreach ($old->requirements as $requirement) {
                    $offer->requirements()->firstOrCreate(['share_item_id' => $requirement->share_item_id], ['quantity' => $requirement->quantity]);
                }
                $states = DB::table('shop_purchase_limit_states')->where('offer_type', $old->getTable())->where('offer_id', $old->id)->get();
                foreach ($states as $state) {
                    $key = ['user_id' => $state->user_id, 'offer_type' => $offer->getTable(), 'offer_id' => $offer->id];
                    $existing = DB::table('shop_purchase_limit_states')->where($key)->first();
                    if ($existing === null) {
                        DB::table('shop_purchase_limit_states')->where('id', $state->id)->update(['offer_type' => $offer->getTable(), 'offer_id' => $offer->id]);
                    } else {
                        // A manually created destination may already have purchases: never reset its allowance.
                        DB::table('shop_purchase_limit_states')->where('id', $existing->id)->update([
                            'period_started_at' => max($existing->period_started_at, $state->period_started_at),
                            'quantity' => $existing->quantity + $state->quantity,
                        ]);
                        DB::table('shop_purchase_limit_states')->where('id', $state->id)->delete();
                    }
                }
                foreach (ReputationShopCart::where('reputation_shop_item_id', $old->id)->get() as $line) {
                    $cart = ShopCart::firstOrNew(['user_id' => $line->user_id, 'shop_item_id' => $offer->id]);
                    $cart->quantity = ($cart->exists ? $cart->quantity : 0) + $line->quantity;
                    $cart->save();
                    $line->delete();
                }
                $old->requirements()->delete();
                $old->delete();
            }
            $this->command?->info('Магазин кладоискателей: структура #'.$shop->id.'; товары и персональные лимиты перенесены.');
        });
    }
}
