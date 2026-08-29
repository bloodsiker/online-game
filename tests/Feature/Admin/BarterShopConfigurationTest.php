<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BarterShopConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('shop');
            $table->string('name');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('npc_id')->nullable();
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('share_structure_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('shop_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('structure_id');
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('share_structure_category_id')->nullable();
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('diamond')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('shop_item_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shop_item_id');
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
        Schema::create('shop_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('structure_id');
            $table->unsignedBigInteger('share_structure_category_id');
            $table->timestamps();
        });

        DB::table('structures')->insert([
            'id' => 1,
            'type' => 'barter_shop',
            'name' => 'Бартерный магазин',
        ]);
        DB::table('share_items')->insert([
            ['id' => 10, 'name' => 'Товар'],
            ['id' => 20, 'name' => 'Первый ресурс'],
            ['id' => 21, 'name' => 'Второй ресурс'],
        ]);
        DB::table('shop_items')->insert([
            'id' => 5,
            'structure_id' => 1,
            'share_item_id' => 10,
        ]);

        $this->withoutMiddleware(AdminMiddleware::class);
    }

    public function test_admin_can_configure_multiple_item_costs(): void
    {
        $this->post(route('admin.structure.info.shop_requirement.add', [1, 5]), [
            'share_item_id' => 20,
            'quantity' => 2,
        ])->assertRedirect();
        $this->post(route('admin.structure.info.shop_requirement.add', [1, 5]), [
            'share_item_id' => 21,
            'quantity' => 3,
        ])->assertRedirect();

        $this->assertDatabaseHas('shop_item_requirements', [
            'shop_item_id' => 5,
            'share_item_id' => 20,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('shop_item_requirements', [
            'shop_item_id' => 5,
            'share_item_id' => 21,
            'quantity' => 3,
        ]);
        $this->assertSame(2, DB::table('shop_item_requirements')->count());
    }

    public function test_admin_can_create_a_barter_shop_structure(): void
    {
        $this->post(route('admin.structure.create'), [
            'name' => 'Лавка редкостей',
            'type' => 'barter_shop',
        ])->assertRedirect();

        $this->assertDatabaseHas('structures', [
            'name' => 'Лавка редкостей',
            'type' => 'barter_shop',
        ]);
    }

    public function test_admin_can_create_and_attach_a_shop_category(): void
    {
        $this->post(route('admin.structure.info.category', 1), [
            'category_name' => 'Редкие ресурсы',
        ])->assertRedirect();

        $categoryId = DB::table('share_structure_categories')
            ->where('name', 'Редкие ресурсы')
            ->value('id');

        $this->assertNotNull($categoryId);
        $this->assertDatabaseHas('shop_categories', [
            'structure_id' => 1,
            'share_structure_category_id' => $categoryId,
        ]);
    }
}
