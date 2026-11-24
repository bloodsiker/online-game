<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Item;

use App\Modules\Item\Domain\Contracts\PlayerBattleStatus;
use App\Modules\Item\Domain\Exceptions\ItemUseBlockedException;
use App\Modules\Item\Domain\Services\ItemUsagePolicyService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\ItemEffectGroup;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemUseLimit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ItemGroupCooldownTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('player_item_use_limit_states', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('share_item_use_limit_id');
            $table->timestamp('period_started_at');
            $table->unsignedSmallInteger('uses_count')->default(0);
            $table->timestamps();
        });
        Schema::create('player_item_group_cooldowns', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('effect_group', 32);
            $table->timestamp('available_at');
            $table->timestamps();
        });
    }

    private function policy(bool $inBattle): ItemUsagePolicyService
    {
        $status = $this->createMock(PlayerBattleStatus::class);
        $status->method('isInBattle')->willReturn($inBattle);

        return new ItemUsagePolicyService($status);
    }

    private function item(int $id, ItemEffectGroup $group, int $periodSeconds): ShareItem
    {
        $item = new ShareItem;
        $item->forceFill(['id' => $id, 'effect_group' => $group->value]);
        $limit = new ShareItemUseLimit(['max_uses' => 1, 'period_seconds' => $periodSeconds]);
        $limit->forceFill(['id' => $id]);
        $item->setRelation('useLimit', $limit);
        $item->setRelation('buffs', collect());

        return $item;
    }

    private function player(): Player
    {
        return (new Player)->forceFill(['id' => 1, 'user_id' => 1]);
    }

    public function test_in_battle_item_blocks_other_items_of_same_group(): void
    {
        $policy = $this->policy(inBattle: true);
        $player = $this->player();
        $weak = $this->item(1, ItemEffectGroup::RECOVERY_POTION, 25);
        $strong = $this->item(2, ItemEffectGroup::RECOVERY_POTION, 120);

        $policy->reserveUse($player, $weak);

        $this->assertNotNull($policy->cooldownUntil($player, $strong));
        $this->expectException(ItemUseBlockedException::class);
        $policy->reserveUse($player, $strong);
    }

    public function test_group_cooldown_length_is_period_of_used_item(): void
    {
        $policy = $this->policy(inBattle: true);
        $player = $this->player();

        $policy->reserveUse($player, $this->item(2, ItemEffectGroup::RECOVERY_POTION, 120));

        $until = $policy->cooldownUntil($player, $this->item(1, ItemEffectGroup::RECOVERY_POTION, 25));
        $this->assertEqualsWithDelta(120, now()->diffInSeconds($until), 2);
    }

    public function test_food_is_not_blocked_by_potion_group(): void
    {
        $policy = $this->policy(inBattle: true);
        $player = $this->player();

        $policy->reserveUse($player, $this->item(1, ItemEffectGroup::RECOVERY_POTION, 25));

        $this->assertNull($policy->cooldownUntil($player, $this->item(3, ItemEffectGroup::RECOVERY_FOOD, 15)));
        $policy->reserveUse($player, $this->item(3, ItemEffectGroup::RECOVERY_FOOD, 15));
        $this->addToAssertionCount(1);
    }

    public function test_out_of_battle_group_cooldown_is_not_applied(): void
    {
        $policy = $this->policy(inBattle: false);
        $player = $this->player();

        $policy->reserveUse($player, $this->item(1, ItemEffectGroup::RECOVERY_POTION, 25));

        $this->assertNull($policy->cooldownUntil($player, $this->item(2, ItemEffectGroup::RECOVERY_POTION, 120)));
        $policy->reserveUse($player, $this->item(2, ItemEffectGroup::RECOVERY_POTION, 120));
        $this->assertSame(0, DB::table('player_item_group_cooldowns')->count());
    }

    public function test_group_cooldown_expires_after_battle(): void
    {
        $player = $this->player();
        $this->policy(inBattle: true)->reserveUse($player, $this->item(1, ItemEffectGroup::RECOVERY_POTION, 25));

        $afterBattle = $this->policy(inBattle: false);

        $this->assertNull($afterBattle->cooldownUntil($player, $this->item(2, ItemEffectGroup::RECOVERY_POTION, 120)));
    }
}
