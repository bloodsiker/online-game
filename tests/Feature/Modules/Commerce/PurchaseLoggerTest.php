<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Commerce;

use App\Modules\Commerce\Application\Services\PurchaseLogger;
use App\Modules\Commerce\Domain\Enums\PurchaseSourceType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PurchaseLoggerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('shop_purchase_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('purchase_uuid');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name');
            $table->unsignedBigInteger('structure_id')->nullable();
            $table->string('structure_name')->nullable();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('unit_diamond');
            $table->unsignedBigInteger('total_price');
            $table->unsignedBigInteger('total_diamond');
            $table->unsignedBigInteger('money_balance_after');
            $table->unsignedBigInteger('diamond_balance_after');
            $table->json('requirements')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_records_purchase_item_structure_cost_and_balance_snapshots(): void
    {
        $user = (new User)->forceFill([
            'id' => 15,
            'name' => 'Покупатель',
            'money' => 750,
            'diamond' => 12,
        ]);
        $structure = (new Structure)->forceFill([
            'id' => 8,
            'name' => 'Лавка алхимика',
            'type' => Structure::TYPE_SHOP,
        ]);
        $item = (new ShareItem)->forceFill(['id' => 101, 'name' => 'Эликсир']);

        $purchaseUuid = app(PurchaseLogger::class)->record(
            user: $user,
            sourceType: PurchaseSourceType::Shop,
            lines: [[
                'item' => $item,
                'quantity' => 2,
                'unit_price' => 125,
                'unit_diamond' => 1,
                'requirements' => [[
                    'share_item_id' => 99,
                    'item_name' => 'Трава',
                    'quantity' => 4,
                ]],
            ]],
            structure: $structure,
            sourceId: $structure->id,
        );

        $this->assertDatabaseHas('shop_purchase_logs', [
            'purchase_uuid' => $purchaseUuid,
            'user_id' => $user->id,
            'user_name' => 'Покупатель',
            'structure_id' => $structure->id,
            'structure_name' => 'Лавка алхимика',
            'source_type' => PurchaseSourceType::Shop->value,
            'share_item_id' => $item->id,
            'item_name' => 'Эликсир',
            'quantity' => 2,
            'total_price' => 250,
            'total_diamond' => 2,
            'money_balance_after' => 750,
            'diamond_balance_after' => 12,
        ]);
    }
}
