<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Quest;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Application\UseCases\CompleteQuest;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Quest\Presentation\Http\QuestController;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesQuestFixture;
use Tests\Support\QuestTestDoubles;
use Tests\TestCase;

class QuestTransactionsTest extends TestCase
{
    use CreatesQuestFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createQuestTables();
        QuestTestDoubles::bind();
    }

    private function complete(Quest $quest, int $revision = 0): bool
    {
        return app(CompleteQuest::class)->execute($this->economyUser(), $quest, 1, $revision);
    }

    public function test_reassigning_a_reputation_quest_invalidates_the_previous_revision(): void
    {
        $this->seedQuestFixture();
        Schema::create('player_reputations', function (Blueprint $table): void {
            $table->id();
            $table->integer('player_id');
            $table->integer('reputation_id');
            $table->integer('points')->default(0);
        });
        Schema::create('reputation_tier_quests', function (Blueprint $table): void {
            $table->id();
            $table->integer('tier_id');
            $table->integer('quest_id');
        });
        DB::table('reputation_tier_quests')->insert(['tier_id' => 1, 'quest_id' => 1]);
        DB::table('quest_players')->where('id', 1)->update(['status' => 'completed', 'revision' => 8]);
        $tier = (new ReputationTier)->forceFill(['id' => 1, 'min_points' => 0]);
        $reputation = (new Reputation)->forceFill(['id' => 1]);
        $reputation->setRelation('tiers', new Collection([$tier]));
        $progress = (new ReputationService)->assignQuest(
            player: Player::findOrFail(1), reputation: $reputation,
        );
        $this->assertSame(9, $progress->revision);
        $this->assertDatabaseCount('quest_players', 1);
        $this->assertDatabaseHas('quest_player_objectives', ['quest_player_id' => 1, 'amount' => 0]);
    }

    public function test_rendered_quest_form_posts_the_progress_revision_with_csrf(): void
    {
        $quest = $this->seedQuestFixture();
        $npc = (new Npc)->forceFill(['id' => 1, 'name' => 'Наставник']);
        $progress = QuestPlayer::findOrFail(1);
        $html = view('quest::quest', [
            'quest' => $quest, 'npc' => $npc, 'inProgress' => true, 'visibleObjectives' => $quest->objectives,
            'currentStage' => null, 'canComplete' => true, 'progressMap' => [1 => 4], 'clanProgress' => null,
            'isAcceptor' => false, 'canAccept' => true, 'dialoguePage' => null, 'dialogueNextUrl' => null,
            'showObjectives' => true, 'stageMessage' => null, 'questPlayer' => $progress,
        ])->render();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//form[@id="quest-complete-form"][@method="post"]/input[@name="revision"][@value="0"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="quest-complete-form"]/input[@name="progress_id"][@value="1"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="quest-complete-form"]/input[@name="_token"]')->length);
        $this->assertSame(['POST'], Route::getRoutes()->getByName('quest.complete')->methods());
        $this->assertSame(['POST'], Route::getRoutes()->getByName('quest.take')->methods());
    }

    public function test_outer_rollback_restores_rewards_and_does_not_send_completion_notice(): void
    {
        $quest = $this->seedQuestFixture();
        DB::beginTransaction();
        $this->complete($quest);
        $this->assertDatabaseCount('quest_test_notifications', 0);
        DB::rollBack();
        $this->assertDatabaseHas('quest_players', ['status' => 'in_progress', 'revision' => 0]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('backpacks', ['count' => 10]);
        $this->assertDatabaseCount('quest_test_notifications', 0);
    }

    public function test_personal_reward_and_consumption_happen_once(): void
    {
        $quest = $this->seedQuestFixture();
        $this->assertFalse($this->complete($quest));
        try {
            $this->complete($quest);
            $this->fail('Replay was accepted.');
        } catch (ValidationException) {
        }
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150]);
        $this->assertDatabaseHas('players', ['id' => 1, 'exp' => 30]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'count' => 8]);
        $this->assertDatabaseHas('quest_players', ['id' => 1, 'status' => 'completed', 'revision' => 1]);
        $this->assertDatabaseCount('quest_test_notifications', 1);
    }

    public function test_clan_rewards_only_go_to_acceptor_once(): void
    {
        $quest = $this->seedQuestFixture(clan: true);
        DB::table('quest_rewards')->insert(['quest_id' => 1, 'type' => 'clan_points', 'amount' => 7]);
        try {
            app(CompleteQuest::class)->execute($this->economyUser(2), $quest, 1, 0);
            $this->fail('Wrong acceptor.');
        } catch (ValidationException) {
        }
        $this->assertFalse($this->complete($quest));
        try {
            $this->complete($quest);
            $this->fail('Replay was accepted.');
        } catch (ValidationException) {
        }
        $this->assertDatabaseHas('clans', ['id' => 1, 'points' => 7]);
        $this->assertDatabaseHas('clan_members', ['user_id' => 1, 'points' => 7]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 100]);
        $this->assertDatabaseCount('quest_test_notifications', 1);
    }

    public function test_last_stage_and_final_reward_roll_back_together_on_failure(): void
    {
        $quest = $this->seedQuestFixture();
        DB::table('quest_stages')->insert(['id' => 1, 'quest_id' => 1, 'grant_share_item_id' => 1, 'grant_amount' => 3]);
        DB::table('quest_players')->update(['current_stage_id' => 1]);
        DB::table('quest_objectives')->update(['stage_id' => 1]);
        DB::statement("CREATE TRIGGER reject_money BEFORE UPDATE OF money ON users BEGIN SELECT RAISE(ABORT, 'Reward failed'); END");
        $this->expectException(QueryException::class);
        try {
            $this->complete($quest);
        } finally {
            $this->assertDatabaseHas('quest_players', ['status' => 'in_progress', 'current_stage_id' => 1, 'revision' => 0]);
            $this->assertDatabaseHas('backpacks', ['count' => 10]);
            $this->assertDatabaseCount('quest_test_notifications', 0);
        }
    }

    public function test_stale_stage_request_cannot_advance_or_grant_again(): void
    {
        $quest = $this->seedQuestFixture();
        DB::table('quest_stages')->insert([
            ['id' => 1, 'quest_id' => 1, 'order' => 1, 'grant_share_item_id' => 1, 'grant_amount' => 3],
            ['id' => 2, 'quest_id' => 1, 'order' => 2, 'grant_share_item_id' => null, 'grant_amount' => 1],
        ]);
        DB::table('quest_players')->update(['current_stage_id' => 1]);
        DB::table('quest_objectives')->update(['stage_id' => 1]);
        $this->assertTrue($this->complete($quest));
        try {
            $this->complete($quest);
            $this->fail('Stale stage accepted.');
        } catch (ValidationException) {
        }
        $this->assertDatabaseHas('quest_players', ['current_stage_id' => 2, 'revision' => 1, 'status' => 'in_progress']);
        $this->assertDatabaseHas('backpacks', ['count' => 9]);
        $this->assertDatabaseHas('users', ['money' => 100]);
    }

    public function test_duplicate_requirements_are_summed_and_can_consume_multiple_stacks(): void
    {
        $quest = $this->seedQuestFixture();
        DB::table('quest_objectives')->insert(['id' => 2, 'quest_id' => 1, 'type' => 'deliver', 'target_id' => 1, 'required_amount' => 7]);
        DB::table('quest_player_objectives')->insert(['quest_player_id' => 1, 'quest_objective_id' => 2]);
        try {
            $this->complete($quest);
            $this->fail('Insufficient combined quantity.');
        } catch (ValidationException) {
        }
        $this->assertDatabaseHas('backpacks', ['count' => 10]);
        DB::table('items')->insert(['id' => 2, 'share_item_id' => 1]);
        DB::table('backpacks')->insert(['user_id' => 1, 'item_id' => 2, 'count' => 2]);
        $this->complete(Quest::findOrFail(1));
        $this->assertSame(3, (int) DB::table('backpacks')->sum('count'));
    }

    public function test_waiting_stage_rejects_early_request_without_resetting_timer(): void
    {
        $quest = $this->seedQuestFixture();
        DB::table('quest_stages')->insert(['id' => 1, 'quest_id' => 1, 'stage_type' => 'wait']);
        DB::table('quest_players')->update(['current_stage_id' => 1, 'current_stage_ready_at' => now()->addHour()]);
        $this->expectException(ValidationException::class);
        try {
            $this->complete($quest);
        } finally {
            $this->assertDatabaseHas('quest_players', ['revision' => 0, 'status' => 'in_progress']);
            $this->assertDatabaseCount('quest_test_notifications', 0);
        }
    }

    public function test_wallet_overflow_rolls_back_status_and_consumed_items(): void
    {
        $quest = $this->seedQuestFixture();
        DB::table('users')->where('id', 1)->update(['money' => 2147483647]);
        $this->expectException(ValidationException::class);
        try {
            $this->complete($quest);
        } finally {
            $this->assertDatabaseHas('quest_players', ['status' => 'in_progress', 'revision' => 0]);
            $this->assertDatabaseHas('backpacks', ['count' => 10]);
            $this->assertDatabaseCount('quest_test_notifications', 0);
        }
    }

    public function test_final_clan_stage_is_consumed_and_rewarded_in_one_action(): void
    {
        $quest = $this->seedQuestFixture(clan: true);
        DB::table('quest_stages')->insert(['id' => 1, 'quest_id' => 1, 'grant_share_item_id' => 1, 'grant_amount' => 3]);
        DB::table('quest_clan_progress')->update(['current_stage_id' => 1]);
        DB::table('quest_objectives')->update(['stage_id' => 1]);
        $this->assertFalse($this->complete($quest));
        $this->assertDatabaseHas('quest_clan_progress', ['status' => 'completed', 'current_stage_id' => null, 'revision' => 1]);
        $this->assertDatabaseHas('backpacks', ['count' => 11]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150]);
        $this->assertDatabaseCount('quest_test_notifications', 1);
    }

    public function test_repeatable_reset_increments_version_and_old_completion_cannot_claim_new_run(): void
    {
        $quest = $this->seedQuestFixture();
        DB::table('quests')->update(['type' => 'repeatable']);
        $this->complete(Quest::findOrFail(1));
        DB::table('quest_players')->update(['reset_at' => now()->subSecond()]);
        Auth::setUser($this->economyUser());
        app(QuestController::class)->take(1, Request::create('/quest/1/take', 'POST', ['npc' => 1]));
        $this->assertDatabaseHas('quest_players', ['revision' => 2, 'status' => 'in_progress']);
        try {
            $this->complete(Quest::findOrFail(1), 0);
            $this->fail('Old run accepted.');
        } catch (ValidationException) {
        }
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150]);
    }
}
