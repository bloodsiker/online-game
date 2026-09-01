<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Item\Application\ItemEffect\ItemEffectStrategyFactory;
use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Player\Domain\Services\PlayerEquipmentLoader;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlayerStatServiceOptimizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('upgrade_lvl')->default(0);
        });
        Schema::connection('sqlite')->create('item_gems', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('socket_index')->default(0);
        });
        Schema::connection('sqlite')->create('item_runes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('slot_index')->default(0);
            $table->text('stats')->nullable();
            $table->text('passive_skill')->nullable();
            $table->unsignedInteger('reroll_count')->default(0);
        });
        Schema::connection('sqlite')->create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::connection('sqlite')->create('share_item_stats', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('stat_type');
            $table->float('value');
            $table->string('value_type');
        });
        Schema::connection('sqlite')->create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_passive')->default(false);
        });
        Schema::connection('sqlite')->create('player_magic_skills', function (Blueprint $table): void {
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamp('cooldown_end_at')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
        });
        Schema::connection('sqlite')->create('player_item_buffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('effect_type');
            $table->float('value');
            $table->string('value_type');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->timestamp('expires_at')->nullable();
        });

        $this->app->forgetScopedInstances();
    }

    public function test_equipment_is_loaded_in_fixed_size_batches(): void
    {
        DB::table('share_items')->insert([
            ['id' => 101, 'name' => 'Sword'],
            ['id' => 102, 'name' => 'Helmet'],
            ['id' => 103, 'name' => 'Gem'],
        ]);
        DB::table('items')->insert([
            ['id' => 11, 'share_item_id' => 101],
            ['id' => 12, 'share_item_id' => 102],
        ]);
        DB::table('item_gems')->insert([
            'id' => 1,
            'item_id' => 11,
            'share_item_id' => 103,
            'socket_index' => 0,
        ]);
        DB::table('share_item_stats')->insert([
            'share_item_id' => 101,
            'stat_type' => 'attack_min',
            'value' => 5,
            'value_type' => 'flat',
        ]);

        $equipment = (new PlayerEquipment)->forceFill([
            'id' => 1,
            'player_id' => 1,
            'hand_left' => 11,
            'helmet' => 12,
        ]);
        $equipment->exists = true;

        $player = $this->player();
        $player->setRelation('playerEquip', $equipment);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $loader = app(PlayerEquipmentLoader::class);
        $loaded = $loader->load($player);
        $firstLoadQueries = count(DB::getQueryLog());
        $loader->load($player);

        $this->assertSame(5, $firstLoadQueries);
        $this->assertCount($firstLoadQueries, DB::getQueryLog());
        $this->assertSame(11, $loaded?->handLeft?->id);
        $this->assertSame(12, $loaded?->helmetSlot?->id);
        $this->assertTrue($loaded->handLeft->relationLoaded('itemInfo'));
        $this->assertTrue($loaded->handLeft->itemInfo->relationLoaded('stats'));
        $this->assertTrue($loaded->handLeft->relationLoaded('gems'));
        $this->assertTrue($loaded->handLeft->relationLoaded('runes'));
        $this->assertTrue($loaded->handLeft->gems->first()->relationLoaded('gemInfo'));
    }

    public function test_stat_sheet_is_cached_until_explicitly_invalidated(): void
    {
        $player = $this->player();
        $player->setRelation('playerEquip', null);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $service = app(PlayerStatService::class);
        $firstSheet = $service->resolve($player);
        $firstResolveQueries = count(DB::getQueryLog());
        $secondSheet = app(PlayerStatService::class)->resolve($player);

        $this->assertSame($service, app(PlayerStatService::class));
        $this->assertSame($firstSheet, $secondSheet);
        $this->assertSame(3, $firstResolveQueries);
        $this->assertCount($firstResolveQueries, DB::getQueryLog());

        $player->strength = 5;
        $service->invalidate($player);
        $updatedSheet = $service->resolve($player);

        $this->assertNotSame($firstSheet, $updatedSheet);
        $this->assertSame(5, $updatedSheet->getStrength());
        $this->assertCount($firstResolveQueries * 2, DB::getQueryLog());
    }

    public function test_weapon_upgrade_scales_only_the_weapons_own_damage(): void
    {
        DB::table('share_items')->insert([
            ['id' => 101, 'name' => 'Sword'],
            ['id' => 102, 'name' => 'Strong Rune'],
        ]);
        DB::table('items')->insert([
            'id' => 11,
            'share_item_id' => 101,
            'upgrade_lvl' => 2,
        ]);
        DB::table('share_item_stats')->insert([
            [
                'share_item_id' => 101,
                'stat_type' => 'attack_min',
                'value' => 10,
                'value_type' => 'flat',
            ],
            [
                'share_item_id' => 101,
                'stat_type' => 'attack_max',
                'value' => 20,
                'value_type' => 'flat',
            ],
        ]);
        DB::table('item_runes')->insert([
            'id' => 1,
            'item_id' => 11,
            'share_item_id' => 102,
            'slot_index' => 0,
            'stats' => json_encode([
                ['stat' => 'attack', 'value' => 100, 'is_percent' => false],
            ], JSON_THROW_ON_ERROR),
        ]);

        $equipment = (new PlayerEquipment)->forceFill([
            'id' => 1,
            'player_id' => 1,
            'hand_left' => 11,
        ]);
        $equipment->exists = true;

        $player = $this->player();
        $player->setRelation('playerEquip', $equipment);

        $sheet = app(PlayerStatService::class)->resolve($player);

        // Base 1–2 + weapon 10–20 + rune 100 + 10% of weapon 1–2.
        // The old implementation also multiplied the rune and player base by 10%.
        $this->assertSame(112, $sheet->getLeftHandMinDmg());
        $this->assertSame(124, $sheet->getLeftHandMaxDmg());
    }

    public function test_item_buff_invalidates_cached_stat_sheet(): void
    {
        $player = $this->player();
        $player->setRelation('playerEquip', null);

        $service = app(PlayerStatService::class);
        $beforeBuff = $service->resolve($player);

        ItemEffectStrategyFactory::make(ItemEffectType::BUFF_ATTACK)->apply(
            $player,
            new ItemEffectValue(
                type: ItemEffectType::BUFF_ATTACK,
                valueType: ItemEffectValueType::FLAT,
                value: 10,
                durationSeconds: 300,
            ),
        );

        $afterBuff = $service->resolve($player);

        $this->assertNotSame($beforeBuff, $afterBuff);
        $this->assertSame(11, $afterBuff->getLeftHandMinDmg());
        $this->assertSame(12, $afterBuff->getLeftHandMaxDmg());
    }

    private function player(): Player
    {
        $player = (new Player)->forceFill([
            'id' => 1,
            'lvl' => 1,
            'free_stats' => 0,
            'strength' => 1,
            'intuition' => 1,
            'agility' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 1,
            'mp_max' => 10,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'intel' => 1,
        ]);
        $player->exists = true;

        return $player;
    }
}
