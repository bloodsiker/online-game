<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\SecretKnowledge;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\SecretKnowledge\Application\Services\SecretKnowledgeService;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

final class SecretKnowledgeServiceTest extends TestCase
{
    private ChatService $chat;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createTables();
        $this->seedBaseRows();
        $this->chat = Mockery::mock(ChatService::class);
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');
        parent::tearDown();
    }

    public function test_analyzing_bundles_consumes_them_and_adds_configured_knowledge_on_success(): void
    {
        $this->settings(bundleShareItemId: 1, chance: 100, gainMin: 2, gainMax: 2);
        $this->backpack(userId: 1, shareItemId: 1, count: 3);

        $result = $this->service()->analyzeBundles($this->user(1), npcId: 1, quantity: 3);

        $this->assertSame(['attempted' => 3, 'successes' => 3, 'gained' => 6, 'points' => 6], $result);
        $this->assertDatabaseHas('player_secret_knowledge', ['player_id' => 1, 'points' => 6]);
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1]);
    }

    public function test_failed_analysis_still_consumes_bundles_without_adding_points(): void
    {
        $this->settings(bundleShareItemId: 1, chance: 0, gainMin: 1, gainMax: 1);
        $this->backpack(userId: 1, shareItemId: 1, count: 2);

        $result = $this->service()->analyzeBundles($this->user(1), npcId: 1, quantity: 2);

        $this->assertSame(['attempted' => 2, 'successes' => 0, 'gained' => 0, 'points' => 0], $result);
        $this->assertDatabaseMissing('player_secret_knowledge', ['player_id' => 1]);
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1]);
    }

    public function test_manual_requires_threshold_and_consumes_one_copy_when_studied(): void
    {
        $this->settings(bundleShareItemId: 1);
        $this->manual();
        $this->backpack(userId: 1, shareItemId: 2, count: 2);
        DB::table('player_secret_knowledge')->insert(['player_id' => 1, 'points' => 9, 'created_at' => now(), 'updated_at' => now()]);

        try {
            $this->service()->studyManual($this->user(1), npcId: 1, manualId: 1);
            $this->fail('Studying should be rejected below the knowledge threshold.');
        } catch (ValidationException) {
            $this->assertDatabaseMissing('player_studied_boss_manuals', ['player_id' => 1, 'boss_knowledge_manual_id' => 1]);
        }

        DB::table('player_secret_knowledge')->where('player_id', 1)->update(['points' => 10]);
        $studied = $this->service()->studyManual($this->user(1), npcId: 1, manualId: 1);

        $this->assertSame(1, $studied->id);
        $this->assertDatabaseHas('player_studied_boss_manuals', ['player_id' => 1, 'boss_knowledge_manual_id' => 1]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'count' => 1]);
    }

    public function test_boss_part_is_granted_once_to_each_group_member_who_studied_the_manual(): void
    {
        $this->settings(bundleShareItemId: 1);
        $this->manual();
        DB::table('player_studied_boss_manuals')->insert([
            ['player_id' => 1, 'boss_knowledge_manual_id' => 1, 'studied_at' => now()],
            ['player_id' => 2, 'boss_knowledge_manual_id' => 1, 'studied_at' => now()],
        ]);

        $party = Mockery::mock(PartyRepositoryInterface::class);
        $party->shouldReceive('getMemberUsers')->twice()->with(7)->andReturn(new Collection([$this->user(1), $this->user(2), $this->user(3)]));
        $this->app->instance(PartyRepositoryInterface::class, $party);

        $backpack = Mockery::mock(BackpackService::class);
        $backpack->shouldReceive('addItemByShareItem')
            ->twice()
            ->with(Mockery::type(User::class), Mockery::on(fn (ShareItem $item): bool => $item->id === 3), 1)
            ->andReturn(new Backpack);
        $this->app->instance(BackpackService::class, $backpack);
        $this->chat->shouldReceive('sendLootToUser')
            ->twice()
            ->with(Mockery::type(User::class), Mockery::type(LocalizedChatMessage::class));

        $service = $this->service();
        $service->awardBossPart(battleId: 11, monsterOnLocationId: 22, monsterId: 50, lootOwner: $this->user(1), lootPartyId: 7);
        $service->awardBossPart(battleId: 11, monsterOnLocationId: 22, monsterId: 50, lootOwner: $this->user(1), lootPartyId: 7);

        $this->assertDatabaseCount('boss_knowledge_reward_claims', 2);
        $this->assertDatabaseHas('boss_knowledge_reward_claims', ['user_id' => 1, 'share_item_id' => 3]);
        $this->assertDatabaseHas('boss_knowledge_reward_claims', ['user_id' => 2, 'share_item_id' => 3]);
        $this->assertDatabaseMissing('boss_knowledge_reward_claims', ['user_id' => 3]);
    }

    private function service(): SecretKnowledgeService
    {
        return new SecretKnowledgeService($this->chat);
    }

    private function user(int $id): User
    {
        return (new User)->forceFill(['id' => $id, 'player_id' => $id, 'location_id' => 4]);
    }

    private function settings(int $bundleShareItemId, int $chance = 25, int $gainMin = 1, int $gainMax = 1): void
    {
        DB::table('secret_knowledge_settings')->insert([
            'id' => 1,
            'npc_id' => 1,
            'bundle_share_item_id' => $bundleShareItemId,
            'analysis_success_chance' => $chance,
            'knowledge_gain_min' => $gainMin,
            'knowledge_gain_max' => $gainMax,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function manual(): void
    {
        DB::table('boss_knowledge_manuals')->insert([
            'id' => 1,
            'manual_share_item_id' => 2,
            'monster_id' => 50,
            'required_knowledge' => 10,
            'boss_part_share_item_id' => 3,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function backpack(int $userId, int $shareItemId, int $count): void
    {
        DB::table('items')->insert(['id' => $shareItemId + 10, 'share_item_id' => $shareItemId, 'count_use' => 0]);
        DB::table('backpacks')->insert([
            'user_id' => $userId,
            'item_id' => $shareItemId + 10,
            'equipped' => false,
            'count' => $count,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedBaseRows(): void
    {
        DB::table('npcs')->insert(['id' => 1, 'location_id' => 4]);
        DB::table('players')->insert([['id' => 1], ['id' => 2], ['id' => 3]]);
        DB::table('monsters')->insert(['id' => 50, 'name' => 'Boss', 'lvl' => 1, 'is_boss' => true]);
        foreach ([
            [1, 'quest'],
            [2, 'boss_manual'],
            [3, 'quest'],
        ] as [$id, $type]) {
            DB::table('share_items')->insert([
                'id' => $id,
                'type' => $type,
                'name' => json_encode(['ru' => "Item {$id}", 'uk' => "Item {$id}"]),
                'is_stackable' => true,
                'count_use' => 0,
            ]);
        }
    }

    private function createTables(): void
    {
        Schema::create('npcs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
        });
        Schema::create('players', fn (Blueprint $table) => $table->id());
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->text('name')->nullable();
            $table->integer('lvl')->default(1);
            $table->boolean('is_boss')->default(false);
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->text('name')->nullable();
            $table->boolean('is_stackable')->default(false);
            $table->integer('count_use')->default(0);
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('location_id');
        });
        Schema::create('secret_knowledge_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('npc_id')->nullable();
            $table->unsignedBigInteger('bundle_share_item_id')->nullable();
            $table->unsignedTinyInteger('analysis_success_chance');
            $table->unsignedInteger('knowledge_gain_min');
            $table->unsignedInteger('knowledge_gain_max');
            $table->boolean('is_active');
            $table->timestamps();
        });
        Schema::create('player_secret_knowledge', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->unique();
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });
        Schema::create('boss_knowledge_manuals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('manual_share_item_id')->unique();
            $table->unsignedBigInteger('monster_id')->unique();
            $table->unsignedInteger('required_knowledge');
            $table->unsignedBigInteger('boss_part_share_item_id');
            $table->boolean('is_active');
            $table->timestamps();
        });
        Schema::create('player_studied_boss_manuals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('boss_knowledge_manual_id');
            $table->timestamp('studied_at');
            $table->unique(['player_id', 'boss_knowledge_manual_id']);
        });
        Schema::create('boss_knowledge_reward_claims', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('monster_on_location_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('share_item_id');
            $table->timestamp('created_at');
            $table->unique(['battle_id', 'monster_on_location_id', 'user_id']);
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->integer('count_use')->default(0);
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped')->default(false);
            $table->unsignedInteger('count')->default(1);
            $table->timestamps();
        });
    }
}
