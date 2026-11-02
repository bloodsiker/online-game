<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\TreasureHunt\Application\Services\AssignTreasureDestination;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class TreasureMapFragmentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (TreasureMapConfig::with('item')->orderBy('required_reputation')->get() as $config) {
                if (ItemAssembly::where('type', 'treasure_map')->whereHas('rewards', fn ($query) => $query->where('share_items.id', $config->share_item_id))->exists()) {
                    continue;
                }
                $map = $config->item;
                $assembly = ItemAssembly::create([
                    'name' => ['ru' => 'Сборка: '.$map->getTranslation('name', 'ru'), 'uk' => 'Збирання: '.$map->getTranslation('name', 'uk')],
                    'type' => 'treasure_map', 'result_mode' => 'fixed', 'is_active' => true, 'sort_order' => $config->required_reputation,
                ]);
                foreach (['I', 'II', 'III', 'IV'] as $number) {
                    $fragment = new ShareItem([
                        'name' => ['ru' => $map->getTranslation('name', 'ru').' — фрагмент '.$number, 'uk' => $map->getTranslation('name', 'uk').' — фрагмент '.$number],
                        'description' => [
                            'ru' => 'Часть карты сокровищ. Соберите четыре разных фрагмента I, II, III и IV этого тира. Нажмите «Собрать карту» в рюкзаке: по одному фрагменту каждого вида превратятся в одну карту.',
                            'uk' => 'Частина карти скарбів. Зберіть чотири різні фрагменти I, II, III та IV цього рівня. Натисніть «Зібрати карту» в рюкзаку: по одному фрагменту кожного виду перетворяться на одну карту.',
                        ],
                        'type' => 'treasure_map_fragment', 'rarity' => $map->rarity,
                        'image' => $map->getRawOriginal('image'), 'price' => 0,
                        'is_active' => true, 'is_stackable' => true, 'is_give' => true, 'is_auction_sellable' => true,
                        'is_clan_warehouse_allowed' => true, 'is_sell' => false, 'is_weight' => true, 'is_use' => false, 'is_droppable' => true,
                    ]);
                    foreach (['name', 'description'] as $field) {
                        if (Schema::hasColumn('share_items', $field.'_legacy')) {
                            $fragment->setAttribute($field.'_legacy', $fragment->getTranslation($field, 'ru'));
                        }
                    }
                    $fragment->save();
                    $assembly->components()->attach($fragment->id, ['count' => 1]);
                }
                $assembly->rewards()->attach($map->id, ['count' => 1, 'weight' => 1]);
                $this->command?->info("Сборка #{$assembly->id}: четыре разных фрагмента → карта #{$map->id}.");
            }
            // Upgrade existing inventory maps without changing previously chosen destinations.
            $existingMaps = DB::table('backpacks')->join('items', 'items.id', '=', 'backpacks.item_id')
                ->join('share_items', 'share_items.id', '=', 'items.share_item_id')
                ->where('share_items.type', 'treasure_map')
                ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('treasure_hunts')->whereColumn('treasure_hunts.map_item_id', 'items.id'))
                ->select('backpacks.user_id', 'backpacks.item_id')->get();
            foreach ($existingMaps as $slot) {
                app(AssignTreasureDestination::class)->execute((int) $slot->user_id, (int) $slot->item_id);
            }
        });
    }
}
