<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ItemController;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ItemDiamondPriceTest extends TestCase
{
    public function test_migration_sets_zero_for_existing_and_new_items(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->integer('price')->default(0);
        });
        DB::table('share_items')->insert(['id' => 1, 'price' => 20]);
        $migration = require database_path('migrations/2026_10_18_000002_add_diamond_price_to_share_items.php');
        $migration->up();
        DB::table('share_items')->insert(['id' => 2, 'price' => 40]);

        $this->assertSame('0.00', ShareItem::findOrFail(1)->diamond_price);
        $this->assertSame('0.00', ShareItem::findOrFail(2)->diamond_price);
        $this->assertSame(20, (int) DB::table('share_items')->where('id', 1)->value('price'));
        $migration->down();
        $this->assertFalse(Schema::hasColumn('share_items', 'diamond_price'));
    }

    public function test_admin_accepts_diamond_price_and_preserves_it_if_field_is_absent(): void
    {
        $item = new ShareItem;
        $method = new \ReflectionMethod(ItemController::class, 'fillItem');
        $data = ['name' => 'Предмет', 'type' => 'misc', 'diamond_price' => '12.50'];
        $method->invoke(new ItemController, $item, Request::create('/admin/item', 'POST', $data));
        $this->assertSame('12.50', $item->diamond_price);
        unset($data['diamond_price']);
        $method->invoke(new ItemController, $item, Request::create('/admin/item', 'POST', $data));
        $this->assertSame('12.50', $item->diamond_price);
        $data['diamond_price'] = '0';
        $method->invoke(new ItemController, $item, Request::create('/admin/item', 'POST', $data));
        $this->assertSame('0.00', $item->diamond_price);
    }

    public function test_admin_rejects_negative_or_overprecise_diamond_price(): void
    {
        foreach (['-1', '1.234', '10000000000', 'abc'] as $value) {
            try {
                (new \ReflectionMethod(ItemController::class, 'fillItem'))->invoke(
                    new ItemController, new ShareItem,
                    Request::create('/admin/item', 'POST', ['name' => 'Предмет', 'type' => 'misc', 'diamond_price' => $value]),
                );
                $this->fail('Invalid diamond price accepted: '.$value);
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('diamond_price', $exception->errors());
            }
        }
    }
}
