<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipDto;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ItemTooltipBatchLoadingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createTables();
        $this->seedBackpackItems(6);
    }

    public function test_tooltip_relations_are_loaded_in_fixed_size_batches(): void
    {
        $singleItemQueries = $this->collectTooltipsFor([1]);
        $sixItemQueries = $this->collectTooltipsFor([1, 2, 3, 4, 5, 6]);

        $this->assertSame($singleItemQueries, $sixItemQueries);
        $this->assertLessThanOrEqual(7, $sixItemQueries);
    }

    public function test_tooltip_contains_remaining_item_uses(): void
    {
        DB::table('share_items')->where('id', 1)->update(['count_use' => 5]);
        DB::table('items')->where('id', 1)->update(['count_use' => 3]);
        DB::table('share_item_use_limits')->insert([
            'share_item_id' => 1,
            'max_uses' => 2,
            'period_seconds' => 86400,
        ]);
        $backpack = Backpack::query()->with('item.itemInfo')->findOrFail(1);

        DB::table('share_item_effects')->insert([
            'share_item_id' => 1,
            'effect_type' => 'restore_lost_exp',
            'value' => 100,
            'value_type' => 'percent',
        ]);

        $collector = Mockery::mock(ItemTooltipCollector::class);
        $collector->shouldReceive('add')
            ->once()
            ->with(Mockery::on(
                fn (ItemTooltipDto $tooltip): bool => $tooltip->toArray()['remainingUses'] === 3
                    && $tooltip->toArray()['specialInfo'] === [[
                        'title' => 'Возврат потерянного при смерти опыта',
                        'value' => '100%',
                    ]]
                    && collect($tooltip->toArray()['stats'])->doesntContain('title', 'Возврат потерянного при смерти опыта')
                    && collect($tooltip->toArray()['stats'])->contains(fn (array $stat): bool => $stat === [
                        'title' => 'Ограничение использования',
                        'value' => '2 раза за 1 день',
                    ]),
            ));

        (new BackpackItemTooltipStrategy([$backpack]))->collect($collector);

        $tooltipScript = file_get_contents(public_path('js/item_tooltip.js'));
        $this->assertStringContainsString('Количество использований', $tooltipScript);
        $this->assertGreaterThan(strpos($tooltipScript, 'a.nosell'), strpos($tooltipScript, 'a.remainingUses'));
    }

    /**
     * @param  list<int>  $ids
     */
    private function collectTooltipsFor(array $ids): int
    {
        $backpacks = Backpack::query()
            ->with('item.itemInfo')
            ->whereIn('id', $ids)
            ->get();

        $collector = Mockery::mock(ItemTooltipCollector::class);
        $collector->shouldReceive('add')->times(count($ids));

        DB::flushQueryLog();
        DB::enableQueryLog();

        (new BackpackItemTooltipStrategy($backpacks))->collect($collector);

        return count(DB::getQueryLog());
    }

    private function seedBackpackItems(int $count): void
    {
        for ($id = 1; $id <= $count; $id++) {
            DB::table('share_items')->insert([
                'id' => $id,
                'type' => 'resource',
                'name' => 'Resource '.$id,
                'rarity' => 'common',
                'is_sell' => true,
                'is_weight' => true,
                'price' => 1,
            ]);
            DB::table('items')->insert([
                'id' => $id,
                'share_item_id' => $id,
            ]);
            DB::table('backpacks')->insert([
                'id' => $id,
                'user_id' => 1,
                'item_id' => $id,
                'equipped' => false,
                'count' => 1,
            ]);
        }
    }

    private function createTables(): void
    {
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('rarity')->default('common');
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_weight')->default(true);
            $table->integer('price')->default(0);
            $table->integer('count_use')->default(0);
            $table->timestamps();
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('upgrade_lvl')->default(0);
            $table->integer('count_use')->default(0);
            $table->timestamps();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped')->default(false);
            $table->unsignedInteger('count')->default(1);
            $table->timestamps();
        });
        Schema::create('share_item_stats', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('stat_type');
            $table->integer('value');
            $table->string('value_type');
            $table->timestamps();
        });
        Schema::create('share_item_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('effect_type');
            $table->integer('value');
            $table->string('value_type');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();
        });
        Schema::create('share_item_use_limits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->unsignedSmallInteger('max_uses');
            $table->unsignedInteger('period_seconds');
            $table->timestamps();
        });
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('share_item_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('type');
            $table->string('stat_key')->nullable();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->unsignedInteger('min_value');
            $table->timestamps();
        });
        Schema::create('item_gems', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('socket_index');
            $table->unsignedBigInteger('share_item_id');
            $table->timestamps();
        });
        Schema::create('item_runes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('slot_index');
            $table->unsignedBigInteger('share_item_id');
            $table->text('stats')->nullable();
            $table->text('passive_skill')->nullable();
            $table->unsignedInteger('reroll_count')->default(0);
            $table->timestamps();
        });
    }
}
