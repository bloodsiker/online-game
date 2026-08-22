<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Quest;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Presentation\Http\QuestController;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ExperienceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class QuestListBatchLoadingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createTables();
        $this->seedQuests(5);
    }

    public function test_current_stages_are_eager_loaded_for_the_whole_page(): void
    {
        $player = (new Player)->forceFill(['id' => 1]);
        $player->exists = true;

        $user = (new User)->forceFill(['id' => 10]);
        $user->exists = true;
        $user->setRelation('player', $player);
        Auth::setUser($user);

        $controller = new QuestController(
            Mockery::mock(BackpackService::class),
            Mockery::mock(ChatService::class),
            Mockery::mock(ExperienceService::class),
            Mockery::mock(ReputationService::class),
        );

        $view = $controller->list(Request::create('/quests', 'GET', ['tab' => 'started']));
        $quests = $view->getData()['quests']->getCollection();

        $this->assertCount(5, $quests);
        $this->assertTrue($quests->every(
            static fn ($questPlayer): bool => $questPlayer->relationLoaded('currentStage'),
        ));
        $this->assertSame([1, 2, 3, 4, 5], $quests->pluck('currentStage.id')->sort()->values()->all());
    }

    private function seedQuests(int $count): void
    {
        for ($id = 1; $id <= $count; $id++) {
            DB::table('quests')->insert([
                'id' => $id,
                'title' => 'Quest '.$id,
                'type' => 'main',
                'is_active' => true,
                'is_finish' => false,
            ]);
            DB::table('quest_stages')->insert([
                'id' => $id,
                'quest_id' => $id,
                'order' => 1,
            ]);
            DB::table('quest_players')->insert([
                'id' => $id,
                'player_id' => 1,
                'quest_id' => $id,
                'status' => 'in_progress',
                'current_stage_id' => $id,
            ]);
        }
    }

    private function createTables(): void
    {
        Schema::create('quests', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');
            $table->unsignedBigInteger('start_npc_id')->nullable();
            $table->unsignedBigInteger('complete_npc_id')->nullable();
            $table->unsignedBigInteger('parent_quest_id')->nullable();
            $table->unsignedBigInteger('after_quest_id')->nullable();
            $table->string('reset_period')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_finish')->default(false);
            $table->timestamps();
        });
        Schema::create('quest_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quest_id');
            $table->unsignedInteger('order');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('quest_objectives', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quest_id');
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->string('type');
            $table->unsignedInteger('required_amount')->default(1);
            $table->timestamps();
        });
        Schema::create('quest_players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('quest_id');
            $table->string('status');
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reset_at')->nullable();
            $table->timestamps();
        });
        Schema::create('quest_player_objectives', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quest_player_id');
            $table->unsignedBigInteger('quest_objective_id');
            $table->unsignedInteger('amount')->default(0);
            $table->timestamps();
        });
        Schema::create('quest_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quest_id');
            $table->string('type');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedInteger('amount')->default(0);
            $table->timestamps();
        });
        Schema::create('quest_dialogues', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quest_id');
            $table->unsignedInteger('order')->default(1);
            $table->text('text')->nullable();
            $table->timestamps();
        });
    }
}
