<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipRenderer;
use App\Modules\Player\Application\ItemTooltip\PlayerInjuryTooltipStrategy;
use App\Modules\Player\Domain\Enums\InjuryBodyPart;
use App\Modules\Player\Domain\Enums\InjurySeverity;
use App\Modules\Player\Domain\Events\PlayerInjured;
use App\Modules\Player\Domain\Services\PlayerInjuryService;
use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlayerInjuryServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('injuries.enabled', true);
        config()->set('injuries.chance_percent', 100);
        DB::purge('sqlite');

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('player_equipments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('helmet')->nullable();
            $table->unsignedBigInteger('hand_left')->nullable();
            $table->unsignedBigInteger('hand_right')->nullable();
            $table->timestamps();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped')->default(false);
            $table->integer('count')->default(1);
            $table->timestamps();
        });
        Schema::create('injury_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('body_part');
            $table->unsignedTinyInteger('severity');
            $table->string('image')->nullable();
            $table->unsignedInteger('duration_seconds');
            $table->unsignedInteger('drop_weight')->default(1);
            $table->json('stat_modifiers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('player_injuries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('injury_type_id')->nullable();
            $table->string('body_part');
            $table->unsignedTinyInteger('severity');
            $table->timestamp('applied_at');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->unique(['player_id', 'body_part']);
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_death_injury_unequips_item_and_occupies_its_slot_until_expiry(): void
    {
        Event::fake([PlayerInjured::class]);
        Carbon::setTestNow('2026-09-07 12:00:00');
        DB::table('players')->insert(['id' => 1, 'user_id' => 10]);
        DB::table('player_equipments')->insert(['player_id' => 1, 'helmet' => 100]);
        DB::table('backpacks')->insert([
            'user_id' => 10,
            'item_id' => 100,
            'equipped' => true,
            'count' => 1,
        ]);
        $injuryType = InjuryType::query()->create([
            'name' => 'Лёгкая травма головы',
            'slug' => 'light-head',
            'body_part' => InjuryBodyPart::HEAD,
            'severity' => InjurySeverity::LIGHT,
            'duration_seconds' => 900,
            'drop_weight' => 100,
            'stat_modifiers' => [
                ['stat' => 'intuition', 'value' => -5, 'is_percent' => true],
            ],
            'is_active' => true,
        ]);

        $injury = $this->service()->inflictAfterDeath(Player::query()->findOrFail(1));

        $this->assertNotNull($injury);
        $this->assertSame(InjuryBodyPart::HEAD, $injury->body_part);
        $this->assertSame(InjurySeverity::LIGHT, $injury->severity);
        $this->assertSame($injuryType->id, $injury->injury_type_id);
        $this->assertSame('2026-09-07 12:15:00', $injury->expires_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('player_equipments', ['player_id' => 1, 'helmet' => null]);
        $this->assertDatabaseHas('backpacks', ['item_id' => 100, 'equipped' => false]);
        $this->assertTrue($this->service()->activeByEquipmentColumn(Player::query()->findOrFail(1))->has('helmet'));
        Event::assertDispatched(
            PlayerInjured::class,
            fn (PlayerInjured $event): bool => $event->player->is($injury->player)
                && $event->injury->is($injury),
        );

        Carbon::setTestNow('2026-09-07 12:15:01');

        $this->assertTrue($this->service()->activeByEquipmentColumn(Player::query()->findOrFail(1))->isEmpty());
        $this->assertSame(1, $this->service()->pruneExpired(Player::query()->findOrFail(1)));
        $this->assertDatabaseMissing('player_injuries', ['player_id' => 1]);
    }

    public function test_injury_is_added_to_the_regular_item_tooltip_collection(): void
    {
        Carbon::setTestNow('2026-09-07 12:00:00');
        DB::table('players')->insert(['id' => 1, 'user_id' => 10]);
        $injuryType = InjuryType::query()->create([
            'name' => 'Перелом левой руки',
            'slug' => 'broken_left_hand',
            'description' => 'Не позволяет держать предмет в левой руке.',
            'body_part' => InjuryBodyPart::LEFT_HAND,
            'severity' => InjurySeverity::SEVERE,
            'image' => '/storage/injuries/broken-left-hand.png',
            'duration_seconds' => 1800,
            'drop_weight' => 100,
            'stat_modifiers' => [
                ['stat' => 'strength', 'value' => -15, 'is_percent' => true],
            ],
            'is_active' => true,
        ]);
        $injury = $injuryType->playerInjuries()->create([
            'player_id' => 1,
            'body_part' => InjuryBodyPart::LEFT_HAND,
            'severity' => InjurySeverity::SEVERE,
            'applied_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        $collector = new ItemTooltipCollector(new ItemTooltipRenderer);
        $collector->collectFrom(new PlayerInjuryTooltipStrategy([$injury]));
        $tooltip = $collector->all()[$injury->tooltipId()];

        $this->assertSame('Перелом левой руки', $tooltip['title']);
        $this->assertSame('Травма: Левая рука', $tooltip['kind']);
        $this->assertSame('Действует ещё: 30:00', $tooltip['diamond']);
        $this->assertSame('/storage/injuries/broken-left-hand.png', $tooltip['image']);
        $this->assertContains(['title' => 'Сила', 'value' => '−15%'], $tooltip['stats']);
    }

    private function service(): PlayerInjuryService
    {
        return new PlayerInjuryService(new class implements RandomizerInterface
        {
            public function between(int $min, int $max): int
            {
                return $min;
            }

            public function chance(float $percent): bool
            {
                return true;
            }
        });
    }
}
