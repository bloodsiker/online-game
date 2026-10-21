<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Auction\Application\Services\AuctionItemSelectionFilter;
use App\Modules\Structure\Auction\Application\UseCases\GetSellableItems;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AuctionItemSelectionFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type');
            $table->boolean('is_auction_sellable');
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped')->default(false);
            $table->integer('count')->default(1);
        });
        foreach ([
            [1, ['ru' => 'Красный камень', 'uk' => 'Червоний камінь'], 'resource', true],
            [2, ['ru' => 'Красный эликсир'], 'potion', true],
            [3, ['ru' => 'Красный камень'], 'resource', false],
        ] as [$id, $name, $type, $allowed]) {
            DB::table('share_items')->insert(['id' => $id, 'name' => json_encode($name), 'type' => $type, 'is_auction_sellable' => $allowed]);
        }
    }

    public function test_search_combines_name_and_type_with_sellability_and_ukrainian_names(): void
    {
        app()->setLocale('uk');
        $filter = app(AuctionItemSelectionFilter::class);
        $this->assertSame([1], $filter->apply(ShareItem::where('is_auction_sellable', true), ['name' => 'Червоний', 'type' => 'resource'])->pluck('id')->all());
        $this->assertSame([2], $filter->apply(ShareItem::where('is_auction_sellable', true), ['name' => 'Красный', 'type' => 'potion'])->pluck('id')->all());
        $this->assertSame([], $filter->apply(ShareItem::query(), ['name' => 'Червоний', 'type' => 'potion'])->pluck('id')->all());
    }

    public function test_lot_filter_keeps_ownership_equipment_and_sellability_guards(): void
    {
        foreach ([[1, 1, 1, false], [2, 1, 1, true], [3, 1, 2, false], [4, 3, 1, false], [5, 2, 1, false]] as [$id, $shareId, $userId, $equipped]) {
            DB::table('items')->insert(['id' => $id, 'share_item_id' => $shareId]);
            DB::table('backpacks')->insert(['item_id' => $id, 'user_id' => $userId, 'equipped' => $equipped]);
        }
        $items = app(GetSellableItems::class)->execute(userId: 1, filter: ['name' => 'Красный', 'type' => 'resource']);
        $this->assertSame([1], $items->pluck('item_id')->all());
    }
}
