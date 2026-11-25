<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Item;

use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LootPartyReservationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('party_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('party_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_ready')->default(false);
            $table->timestamps();
        });
        DB::table('party_members')->insert([
            ['party_id' => 10, 'user_id' => 1],
            ['party_id' => 10, 'user_id' => 2],
        ]);
    }

    private function loot(?int $ownerId, ?int $partyId, bool $active = true): ItemOnLocation
    {
        return (new ItemOnLocation)->forceFill([
            'loot_owner_id' => $ownerId,
            'loot_party_id' => $partyId,
            'loot_reserved_until' => $active ? now()->addMinute() : now()->subSecond(),
        ]);
    }

    public function test_single_owner_loot_is_reserved_for_everyone_else(): void
    {
        $loot = $this->loot(1, null);

        $this->assertFalse($loot->isReservedForAnotherUser(1));
        $this->assertTrue($loot->isReservedForAnotherUser(2));
        $this->assertTrue($loot->isReservedForAnotherUser(3));
    }

    public function test_group_loot_is_open_to_every_party_member_but_not_to_outsiders(): void
    {
        $loot = $this->loot(1, 10);

        $this->assertFalse($loot->isReservedForAnotherUser(1));
        $this->assertFalse($loot->isReservedForAnotherUser(2), 'второй участник группы может подобрать');
        $this->assertTrue($loot->isReservedForAnotherUser(3), 'посторонний ждёт, пока истечёт минута');
    }

    public function test_reservation_expires_after_a_minute(): void
    {
        $this->assertFalse($this->loot(1, 10, active: false)->isReservedForAnotherUser(3));
        $this->assertFalse($this->loot(null, null)->isReservedForAnotherUser(3));
    }
}
