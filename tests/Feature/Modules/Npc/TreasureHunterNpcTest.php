<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Npc;

use App\Modules\Influence\Domain\Services\MapInfluenceRequirementService;
use App\Modules\Npc\Application\UseCases\GetNpcPage;
use App\Modules\Npc\Domain\Contracts\NpcReadRepository;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Database\Seeders\TreasureHunterNpcSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class TreasureHunterNpcTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
    }

    public function test_seed_links_npc_to_reputation_at_location_four_without_duplicates_or_resetting_admin_edits(): void
    {
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('npcs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->json('name');
            $table->json('description');
            $table->string('image')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->boolean('hide_location');
            $table->boolean('is_active');
            $table->timestamps();
        });
        Schema::create('reputations', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->unsignedBigInteger('npc_id')->nullable();
            $table->timestamps();
        });
        Schema::create('treasure_map_configs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reputation_id');
        });
        Schema::create('share_structure_categories', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->boolean('is_active');
            $table->timestamps();
        });
        Schema::create('reputation_categories', function (Blueprint $table): void {
            $table->unsignedBigInteger('reputation_id');
            $table->unsignedBigInteger('share_structure_category_id');
            $table->primary(['reputation_id', 'share_structure_category_id']);
        });
        DB::table('locations')->insert([['id' => 4], ['id' => 6]]);
        $reputation = Reputation::create(['name' => ['ru' => 'Кладоискатель', 'uk' => 'Шукач скарбів']]);
        TreasureMapConfig::insert(['reputation_id' => $reputation->id]);
        (new TreasureHunterNpcSeeder)->run();
        $npc = $reputation->fresh()->npc;
        $this->assertSame(4, (int) $npc->location_id);
        $this->assertTrue($npc->is_active);
        $this->assertFalse($npc->hide_location);
        $this->assertSame('Шукач скарбів Ренвар', $npc->getTranslation('name', 'uk'));
        $this->assertSame(1, $reputation->fresh()->categories->count());
        $npc->update(['location_id' => 6, 'name' => ['ru' => 'Новое имя']]);
        (new TreasureHunterNpcSeeder)->run();
        $this->assertDatabaseCount('npcs', 1);
        $this->assertDatabaseCount('share_structure_categories', 1);
        $this->assertDatabaseCount('reputation_categories', 1);
        $this->assertSame(6, (int) $npc->fresh()->location_id);
        $this->assertSame('Новое имя', $npc->fresh()->getTranslation('name', 'ru'));
    }

    public function test_npc_offers_configured_empty_shop_and_existing_shop_but_not_unconfigured_or_disabled_categories(): void
    {
        Schema::create('npc_influence_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('npc_id');
        });
        $player = (new Player)->forceFill(['id' => 10]);
        $user = (new User)->forceFill(['id' => 1])->setRelation('player', $player)->setRelation('clanMembership', null);
        $npc = (new Npc)->forceFill(['id' => 5, 'name' => ['ru' => 'Ренвар']])->setRelation('structures', new Collection);
        $reputations = collect();
        foreach ([1, 2, 3, 4] as $id) {
            $reputation = (new Reputation)->forceFill(['id' => $id]);
            $reputation->setRelation('shopItems', new Collection($id === 3 ? [(object) ['id' => 1]] : []));
            $reputation->setRelation('categories', new Collection($id < 3 ? [new ShareStructureCategory(['is_active' => $id === 1])] : []));
            $reputations->push($reputation);
        }
        $repository = \Mockery::mock(NpcReadRepository::class);
        $repository->shouldReceive('findNpcByIdOrFail')->with(5)->andReturn($npc);
        $repository->shouldReceive('getPlayerQuestStateGroups')->andReturn(['completed' => [], 'in_progress' => [], 'repeatable_ready' => []]);
        foreach (['getQuestsOnCooldown', 'getAvailableQuests', 'getInProgressQuestPlayers'] as $method) {
            $repository->shouldReceive($method)->andReturn(collect());
        }
        $repository->shouldReceive('getNpcReputations')->andReturn($reputations);
        $repository->shouldReceive('getStartDialogueNode')->andReturnNull();
        $service = \Mockery::mock(ReputationService::class);
        $service->shouldReceive('getOrZero')->times(8)->andReturn(new PlayerReputation(['points' => 0]));
        $service->shouldNotReceive('getOrCreate');
        $service->shouldReceive('getCurrentTier')->andReturnNull();
        $service->shouldReceive('getAvailableFeatQuest')->andReturnNull();
        $progress = \Mockery::mock(QuestProgressService::class);
        $progress->shouldReceive('progressTalk')->twice()->with($player, 5);
        $influence = new MapInfluenceRequirementService;
        $page = (new GetNpcPage($repository, $service, $progress, $influence))->execute($user, 5);
        $this->assertSame([1, 3], $page->reputationShops->pluck('id')->all());
        $npc->setRelation('structures', new Collection([(new Structure)->forceFill(['reputation_id' => 1])]));
        $page = (new GetNpcPage($repository, $service, $progress, $influence))->execute($user, 5);
        $this->assertSame([3], $page->reputationShops->pluck('id')->all());
    }
}
