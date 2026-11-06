<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\StructureController;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\PremiumShop\Application\UseCases\GetShopItems;
use App\Modules\Structure\Shop\Application\UseCases\ReorderShopItems;
use App\Modules\Structure\Shop\Infrastructure\Persistence\EloquentShopReadRepository;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ShopItemOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('shop');
        });
        Schema::create('shop_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('structure_id');
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('share_structure_category_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('price')->default(20);
            $table->integer('diamond')->default(3);
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('share_item_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
        });
        Schema::create('shop_item_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shop_item_id');
            $table->unsignedBigInteger('share_item_id');
        });
        DB::table('structures')->insert([['id' => 22], ['id' => 23]]);
        DB::table('share_items')->insert(['id' => 1, 'name' => 'Товар']);
        foreach ([[11, 22, 1], [12, 22, 1], [13, 22, 2], [14, 23, 1], [15, 22, null], [16, 22, null]] as [$id, $structure, $category]) {
            DB::table('shop_items')->insert(['id' => $id, 'structure_id' => $structure, 'share_structure_category_id' => $category, 'share_item_id' => 1, 'sort_order' => 10]);
        }
    }

    public function test_saved_category_order_is_used_by_normal_and_premium_shop(): void
    {
        $response = (new StructureController)->reorderShopItems(
            request: Request::create('/admin/structure/22/shop/order', 'POST', ['category_id' => '1', 'item_ids' => ['12', '11']]),
            structure: Structure::findOrFail(22), reorder: app(ReorderShopItems::class),
        );
        $this->assertSame(route('admin.structure.info', 22).'#tab-shop', $response->getTargetUrl());
        $this->assertSame([12, 11], app(GetShopItems::class)->execute(22, 1)->pluck('id')->all());
        $this->assertSame([12, 11], app(EloquentShopReadRepository::class)->getShopItems(22, 1)->pluck('id')->all());
        $this->assertDatabaseHas('shop_items', ['id' => 12, 'sort_order' => 2, 'price' => 20, 'diamond' => 3, 'share_structure_category_id' => 1]);
        foreach ([13, 14, 15, 16] as $id) {
            $this->assertDatabaseHas('shop_items', ['id' => $id, 'sort_order' => 10]);
        }
        $this->assertSame('admin.structure.info.shop_order', Route::getRoutes()->match(Request::create(route('admin.structure.info.shop_order', 22), 'POST'))->getName());
    }

    public function test_uncategorized_items_can_be_reordered_separately(): void
    {
        app(ReorderShopItems::class)->execute(Structure::findOrFail(22), null, collect([16, 15]));
        $this->assertSame([16, 15], DB::table('shop_items')->where('structure_id', 22)->whereNull('share_structure_category_id')->orderByDesc('sort_order')->pluck('id')->all());
        $this->assertDatabaseHas('shop_items', ['id' => 11, 'sort_order' => 10]);
    }

    public function test_incomplete_duplicate_foreign_and_other_category_lists_do_not_write(): void
    {
        foreach ([[11], [11, 11], [11, 14], [11, 13], [11, 12, 15]] as $ids) {
            try {
                app(ReorderShopItems::class)->execute(Structure::findOrFail(22), 1, collect($ids));
                $this->fail('Invalid shop order accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('item_ids', $exception->errors());
            }
        }
        $this->assertSame(60, (int) DB::table('shop_items')->sum('sort_order'));
    }

    public function test_failed_write_rolls_back_the_entire_category_order(): void
    {
        DB::unprepared("CREATE TRIGGER reject_shop_order BEFORE UPDATE ON shop_items WHEN NEW.id = 11 BEGIN SELECT RAISE(ABORT, 'injected order failure'); END");
        try {
            app(ReorderShopItems::class)->execute(Structure::findOrFail(22), 1, collect([12, 11]));
            $this->fail('Write failure must abort the ordering.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('injected order failure', $exception->getMessage());
        }
        $this->assertDatabaseHas('shop_items', ['id' => 12, 'sort_order' => 10]);
        $this->assertDatabaseHas('shop_items', ['id' => 11, 'sort_order' => 10]);
    }

    public function test_category_table_renders_its_own_order_form_and_drag_handles(): void
    {
        $structure = Structure::findOrFail(22)->setRelation('categories', collect());
        $items = app(GetShopItems::class)->execute(22, 1);
        $html = view('admin.structures.partials.shop_items_table', ['structure' => $structure, 'items' => $items, 'categoryId' => 1])->render();
        $this->assertStringContainsString(route('admin.structure.info.shop_order', 22), $html);
        $this->assertStringContainsString('name="category_id" value="1"', $html);
        $this->assertStringContainsString('data-shop-sort-id="11"', $html);
        $this->assertStringNotContainsString('data-shop-sort-id="13"', $html);
        $this->assertSame(2, substr_count($html, 'data-shop-sort-handle draggable'));
    }
}
