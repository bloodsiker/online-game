<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\Services\MonsterLootService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonsterLootSharesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('battle_monster_damage', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('location_monster_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('damage');
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
        });
        foreach ([1, 2, 3, 4] as $id) {
            DB::table('users')->insert(['id' => $id, 'name' => 'U'.$id]);
        }
    }

    /** @param  array<int, int|null>  $parties  user_id => party_id */
    private function service(array $parties = []): MonsterLootService
    {
        return new MonsterLootService(fn (int $userId): ?int => $parties[$userId] ?? null);
    }

    private function hit(int $userId, int $damage): void
    {
        DB::table('battle_monster_damage')->insert(['battle_id' => 1, 'location_monster_id' => 1, 'user_id' => $userId, 'damage' => $damage]);
    }

    public function test_money_is_split_proportionally_to_damage(): void
    {
        $this->hit(1, 3500);
        $this->hit(2, 3500);
        $this->hit(3, 3000);

        $shares = collect($this->service()->moneyShares(1, 1, 100000, 3))->keyBy('user_id');

        $this->assertSame(35000, $shares[1]['amount']);
        $this->assertSame(35000, $shares[2]['amount']);
        $this->assertSame(30000, $shares[3]['amount']);
        $this->assertSame(100000, $shares->sum('amount'));
        $this->assertSame(35, $shares[1]['percent']);
    }

    public function test_rounding_remainder_goes_to_the_top_damage_dealer_and_nothing_is_lost(): void
    {
        $this->hit(1, 1);
        $this->hit(2, 1);
        $this->hit(3, 1);

        $shares = $this->service()->moneyShares(1, 1, 100, 3);

        $this->assertSame(100, array_sum(array_column($shares, 'amount')));
        $this->assertSame(34, $shares[0]['amount']);
    }

    public function test_without_damage_records_the_killer_gets_everything(): void
    {
        $this->assertSame(
            [['user_id' => 4, 'amount' => 500, 'percent' => 100]],
            $this->service()->moneyShares(1, 1, 500, 4),
        );
        $this->assertSame([], $this->service()->moneyShares(1, 1, 0, 4));
    }

    public function test_group_damage_is_summed_and_wins_loot_over_a_higher_solo_hitter(): void
    {
        // Группа: игроки 1 и 2 по 3 500 = 7 000; одиночка 3 бьёт 3 000 и в одиночку — главный бьющий.
        $this->hit(1, 3500);
        $this->hit(2, 3600);
        $this->hit(3, 6000);

        $owner = $this->service([1 => 10, 2 => 10])->lootOwner(1, 1, (new User)->forceFill(['id' => 3]));

        $this->assertSame(10, $owner['party_id']);
        $this->assertSame(2, (int) $owner['user']->id, 'автолут уходит самому бьющему из группы');
    }

    public function test_solo_player_wins_loot_when_group_total_is_smaller(): void
    {
        $this->hit(1, 1000);
        $this->hit(2, 1000);
        $this->hit(3, 3000);

        $owner = $this->service([1 => 10, 2 => 10])->lootOwner(1, 1, (new User)->forceFill(['id' => 3]));

        $this->assertNull($owner['party_id']);
        $this->assertSame(3, (int) $owner['user']->id);
    }

    public function test_loot_owner_without_damage_falls_back_to_the_killer(): void
    {
        $owner = $this->service()->lootOwner(1, 1, (new User)->forceFill(['id' => 4]));

        $this->assertSame(4, (int) $owner['user']->id);
        $this->assertNull($owner['party_id']);
    }
}
