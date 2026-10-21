<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Dungeon;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\UseCases\LeaveClanBossBattle;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Chat\Application\Listeners\SendClanBossRewardMessage;
use App\Modules\Chat\Application\Services\ChatMessageTranslator;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanLog;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Clan\Domain\Models\ClanRole;
use App\Modules\Clan\Domain\Services\ClanExperienceService;
use App\Modules\Clan\Domain\Services\ClanLevelService;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\Dungeon\Application\Services\ClanBossRewardRecorder;
use App\Modules\Dungeon\Application\Services\ClanBossRewardService;
use App\Modules\Dungeon\Application\Services\ClanBossService;
use App\Modules\Dungeon\Domain\Enums\DungeonRewardType;
use App\Modules\Dungeon\Domain\Enums\DungeonType;
use App\Modules\Dungeon\Domain\Events\ClanBossDefeated;
use App\Modules\Dungeon\Domain\Events\ClanBossRewardGranted;
use App\Modules\Dungeon\Domain\Events\ClanBossRunExpired;
use App\Modules\Dungeon\Domain\Events\ClanBossRunStarted;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossContribution;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossCooldown;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossPlayerAttackState;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRewardReceipt;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonReward;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\BossDefeatLog;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Notification\Application\Services\PlayerNotificationService;
use App\Modules\Notification\Domain\Events\PlayerNotificationAvailable;
use App\Modules\Notification\Infrastructure\Persistence\Models\PlayerNotification;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\ClanBoss\Presentation\Http\ClanBossController;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Domain\Events\UserLocationChanged;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ClanBossServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createSchema();
        (require database_path('migrations/2026_10_17_000008_create_clan_boss_reward_receipts.php'))->up();
        Event::fake([ClanBossRunStarted::class, ClanBossDefeated::class, ClanBossRunExpired::class, UserLocationChanged::class, ClanBossRewardGranted::class]);
    }

    public function test_open_run_spawns_shared_instance_and_second_open_fails(): void
    {
        [$service, $fixtures] = $this->makeService();

        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        Event::assertDispatched(ClanBossRunStarted::class, fn (ClanBossRunStarted $event): bool => $event->clanId === $fixtures['clan']->id
            && ($event->bossNameTranslations['ru'] ?? null) === 'Босс 1');

        $this->assertTrue($run->isActive());
        $this->assertSame($fixtures['stage1']->id, $run->current_stage_id);

        $state = ClanBossRunStage::query()->where('clan_boss_run_id', $run->id)->firstOrFail();
        $this->assertSame(1000, (int) $state->hp_max);
        $this->assertNull($state->expires_at);

        $instance = MonsterOnLocation::query()->findOrFail($state->monster_on_location_id);
        $this->assertSame(1, (int) $instance->active);
        $this->assertSame(0, (int) $instance->aggression);
        $this->assertSame($state->id, (int) $instance->clan_boss_stage_state_id);

        $this->expectException(RuntimeException::class);
        $service->openRun($fixtures['structure'], $fixtures['leader']);
    }

    public function test_open_run_requires_minimum_level_and_presence_at_lair(): void
    {
        [$service, $fixtures] = $this->makeService();
        $fixtures['dungeon']->update(['min_level' => 5]);

        try {
            $service->openRun($fixtures['structure'], $fixtures['leader']);
            $this->fail('Ожидалась проверка минимального уровня.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('уровень 5', $e->getMessage());
        }

        $fixtures['leaderPlayer']->forceFill(['lvl' => 5])->save();
        $otherLocation = Location::query()->forceCreate(['name' => 'Другая локация']);
        $fixtures['leader']->update(['location_id' => $otherLocation->id]);

        try {
            $service->openRun($fixtures['structure'], $fixtures['leader']);
            $this->fail('Ожидалась проверка присутствия у логова.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('только у логова', $e->getMessage());
        }
    }

    public function test_start_permission_is_required_to_open_run_but_not_to_attack(): void
    {
        [$service, $fixtures] = $this->makeService();

        $roleWithoutPermission = ClanRole::query()->create([
            'clan_id' => $fixtures['clan']->id, 'name' => 'Новичок', 'permissions' => 0, 'is_leader' => false,
        ]);
        $outsider = User::query()->create(['name' => 'Новичок', 'location_id' => $fixtures['leader']->location_id]);
        $outsiderPlayer = Player::query()->forceCreate(['user_id' => $outsider->id, 'exp' => 0, 'lvl' => 1]);
        $outsider->forceFill(['player_id' => $outsiderPlayer->id])->save();
        ClanMember::query()->forceCreate([
            'clan_id' => $fixtures['clan']->id, 'user_id' => $outsider->id, 'role_id' => $roleWithoutPermission->id,
            'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10),
        ]);

        try {
            $service->openRun($fixtures['structure'], $outsider);
            $this->fail('Ожидалась проверка права "Начинать бой с клан-боссом".');
        } catch (RuntimeException $e) {
            $this->assertSame('У вас нет прав начинать бой с клан-боссом.', $e->getMessage());
        }

        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $boss = $this->currentBoss($run);
        $state = $service->assertCanHit($outsider, $boss);
        $this->assertTrue($state->isActive());
    }

    public function test_cooldown_on_any_stage_blocks_a_new_run(): void
    {
        [$service, $fixtures] = $this->makeService();
        ClanBossCooldown::query()->create([
            'dungeon_stage_id' => $fixtures['stage2']->id,
            'clan_id' => $fixtures['clan']->id,
            'available_at' => now()->addHour(),
        ]);

        try {
            $service->openRun($fixtures['structure'], $fixtures['leader']);
            $this->fail('Ожидалась проверка КД второго босса.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Босс будет доступен', $e->getMessage());
        }

        $this->assertSame(0, ClanBossRun::query()->count());
    }

    public function test_forbidden_boss_drop_is_not_sent_to_a_player_backpack(): void
    {
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        ClanBossContribution::query()->create([
            'clan_boss_run_id' => $run->id,
            'dungeon_stage_id' => $fixtures['stage1']->id,
            'user_id' => $fixtures['leader']->id,
            'damage' => 100,
            'hits' => 1,
        ]);

        $item = ShareItem::query()->forceCreate([
            'name' => 'Запретный предмет',
            'is_clan_warehouse_allowed' => false,
        ]);
        DB::table('monster_has_items')->insert([
            'monster_id' => $fixtures['boss1']->id,
            'share_item_id' => $item->id,
            'drop_chance' => 100,
            'min_count' => 1,
            'max_count' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fixtures['rewardBackpack']->shouldNotReceive('addItemByShareItem');
        $method = new \ReflectionMethod(ClanBossRewardService::class, 'grantBossDrops');
        $dropped = $method->invoke($fixtures['rewardService'], $run, $fixtures['boss1'], $fixtures['leader']);

        $this->assertSame(['names' => [], 'rewards' => []], $dropped);
    }

    public function test_attack_batch_is_available_until_spent_then_recovers_after_cooldown(): void
    {
        [$service, $fixtures] = $this->makeService();
        $fixtures['dungeon']->update([
            'clan_boss_hit_batch_size' => 2,
            'clan_boss_hit_batch_cooldown_seconds' => 21600,
            'time_limit_seconds' => 36000,
        ]);
        $fixtures['stage1']->update(['time_limit_seconds' => null]);
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $boss = $this->currentBoss($run);

        $state = $service->assertCanHit($fixtures['member'], $boss);
        $service->recordHit($fixtures['member'], $state, 100);
        // There is no cooldown between hits within a batch.
        $state = $service->assertCanHit($fixtures['member'], $boss);
        $service->recordHit($fixtures['member'], $state, 50);

        $contribution = ClanBossContribution::query()->firstOrFail();
        $this->assertSame(150, (int) $contribution->damage);
        $this->assertSame(2, (int) $contribution->hits);

        $this->travel(26)->minutes();
        try {
            $service->assertCanHit($fixtures['member'], $boss);
            $this->fail('Ожидалось восстановление пакета после его расходования.');
        } catch (RuntimeException $e) {
            $this->assertSame('Пакет ударов восстановится через 5ч 34м.', $e->getMessage());
        }

        $this->travel(6)->hours();
        $state = $service->assertCanHit($fixtures['member'], $boss);
        $service->recordHit($fixtures['member'], $state, 25);

        $this->assertSame(1, (int) ClanBossPlayerAttackState::query()->firstOrFail()->hits_in_batch);
        $this->assertSame(175, (int) $contribution->fresh()->damage);
    }

    public function test_attack_endpoint_rejects_player_whose_attack_batch_is_recharging(): void
    {
        [$service, $fixtures] = $this->makeService();
        $now = now()->startOfMinute();
        $this->travelTo($now);
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        ClanBossPlayerAttackState::query()->create([
            'clan_boss_run_id' => $run->id,
            'user_id' => $fixtures['leader']->id,
            'hits_in_batch' => 10,
            'cooldown_until' => $now->copy()->addHours(5)->addMinutes(34),
        ]);
        $this->withoutMiddleware();
        $response = $this->actingAs($fixtures['leader'])
            ->post(route('clan_boss.attack', ['id' => $fixtures['structure']->id]));

        $response->assertRedirect(route('clan_boss', ['id' => $fixtures['structure']->id]))
            ->assertSessionHas('message', fn (string $message): bool => str_contains($message, 'Пакет ударов восстановится через 5 часов 34 минуты.'));
    }

    public function test_attack_batch_cooldown_is_shared_across_boss_stages_in_the_run(): void
    {
        [$service, $fixtures] = $this->makeService();
        $fixtures['dungeon']->update([
            'clan_boss_hit_batch_size' => 1,
            'clan_boss_hit_batch_cooldown_seconds' => 60,
        ]);
        $fixtures['stage2']->update(['time_limit_seconds' => null]);
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $boss = $this->currentBoss($run);

        $stageState = $service->assertCanHit($fixtures['leader'], $boss);
        $service->recordHit($fixtures['leader'], $stageState, 1000);
        $boss->update(['hp_now' => 0]);
        $service->handleStageKill($boss, $fixtures['leaderPlayer'], $fixtures['battleId'], new AttackResultDTO);

        $nextBoss = $this->currentBoss($run->fresh());
        try {
            $service->assertCanHit($fixtures['leader'], $nextBoss);
            $this->fail('Переход на следующего босса не должен сбрасывать КД пакета.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Пакет ударов восстановится', $e->getMessage());
        }

        $this->travel(1)->minute();
        $this->assertNotNull($service->assertCanHit($fixtures['leader'], $nextBoss));
    }

    public function test_safe_exit_from_clan_boss_battle_preserves_unused_attacks_and_run(): void
    {
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $attackState = ClanBossPlayerAttackState::query()->create([
            'clan_boss_run_id' => $run->id,
            'user_id' => $fixtures['leader']->id,
            'hits_in_batch' => 3,
        ]);
        $boss = $this->currentBoss($run);
        $battle = Battle::query()->create([
            'location_id' => $fixtures['structure']->location_id,
            'status' => BattleStatus::ACTIVE,
            'rounds' => 1,
        ]);
        $participant = BattleDetail::query()->create([
            'battle_id' => $battle->id,
            'user_id' => $fixtures['leader']->id,
        ]);
        BattleDetail::query()->create([
            'battle_id' => $battle->id,
            'location_monster_id' => $boss->id,
        ]);

        $structureId = (new LeaveClanBossBattle)->execute($fixtures['leader'], $battle->id);

        $this->assertSame($fixtures['structure']->id, $structureId);
        $this->assertDatabaseMissing('battle_details', ['id' => $participant->id]);
        $this->assertSame(BattleStatus::FINISH, $battle->fresh()->status);
        $this->assertSame(3, (int) $attackState->fresh()->hits_in_batch);
        $this->assertTrue($run->fresh()->isActive());
    }

    public function test_kill_grants_rewards_to_every_damage_contributor_and_advances(): void
    {
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $boss = $this->currentBoss($run);

        // Второй участник наносит только 5 урона, но тоже получает награду.
        $state = $service->assertCanHit($fixtures['leader'], $boss);
        $service->recordHit($fixtures['leader'], $state, 950);
        $state = $service->assertCanHit($fixtures['member'], $boss);
        $service->recordHit($fixtures['member'], $state, 5);

        $boss->hp_now = 0;
        $boss->save();

        $log = new AttackResultDTO;
        $service->handleStageKill($boss, $fixtures['leaderPlayer'], $fixtures['battleId'], $log);
        Event::assertDispatched(ClanBossDefeated::class, fn (ClanBossDefeated $event): bool => $event->clanId === $fixtures['clan']->id
            && ($event->bossNameTranslations['ru'] ?? null) === 'Босс 1');

        $this->assertSame(50, (int) $fixtures['leader']->fresh()->money);
        $this->assertSame(50, (int) $fixtures['member']->fresh()->money);

        $this->assertTrue((bool) ClanBossContribution::query()
            ->where('user_id', $fixtures['leader']->id)->value('rewarded'));
        $this->assertTrue((bool) ClanBossContribution::query()
            ->where('user_id', $fixtures['member']->id)->value('rewarded'));

        // Идемпотентность: повторный вызов не дублирует награды.
        $service->handleStageKill($boss, $fixtures['leaderPlayer'], $fixtures['battleId'], new AttackResultDTO);
        $this->assertSame(50, (int) $fixtures['leader']->fresh()->money);

        // Переход на второй этап.
        $run = $run->fresh();
        $this->assertSame($fixtures['stage2']->id, $run->current_stage_id);
        $this->assertSame(1, BossDefeatLog::query()->count());
        $this->assertSame(1, ClanLog::query()->count());

        // Второй босс: убийство завершает поход.
        $boss2 = $this->currentBoss($run);
        $state2 = $service->assertCanHit($fixtures['leader'], $boss2);
        $this->assertNull($state2->expires_at);
        $service->recordHit($fixtures['leader'], $state2, 2000);
        $boss2->hp_now = 0;
        $boss2->save();
        $service->handleStageKill($boss2, $fixtures['leaderPlayer'], $fixtures['battleId'], new AttackResultDTO);

        $this->assertSame(ClanBossRun::STATUS_COMPLETED, $run->fresh()->status);
    }

    public function test_expired_run_is_closed_by_scheduler(): void
    {
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $run->update(['expires_at' => now()->subMinute()]);

        $processed = $service->expireOverdue(now());

        $this->assertSame(1, $processed);
        $this->assertSame(ClanBossRun::STATUS_EXPIRED, $run->fresh()->status);
        Event::assertDispatched(ClanBossRunExpired::class, fn (ClanBossRunExpired $event): bool => $event->clanId === $fixtures['clan']->id);
    }

    public function test_clan_boss_ignores_legacy_stage_timer_until_the_whole_run_expires(): void
    {
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $boss = $this->currentBoss($run);
        $state = ClanBossRunStage::query()->where('clan_boss_run_id', $run->id)->firstOrFail();
        $this->assertNull($state->expires_at);

        // Эмулируем уже открытый поход со старым отдельным таймером босса.
        $state->update(['expires_at' => now()->subMinute()]);
        $this->assertSame($state->id, $service->assertCanHit($fixtures['leader'], $boss)->id);
        $this->assertSame(0, $service->expireOverdue(now()));
        $this->assertSame(ClanBossRunStage::STATUS_ACTIVE, $state->fresh()->status);
        $this->assertSame(1, (int) $boss->fresh()->active);
        $this->assertSame($fixtures['stage1']->id, $run->fresh()->current_stage_id);

        $this->travel(31)->minutes();
        $this->assertSame(0, $service->expireOverdue(now()));
        $this->assertSame($state->id, $service->assertCanHit($fixtures['leader'], $boss)->id);

        $run->update(['expires_at' => now()->subSecond()]);
        $this->assertSame(1, $service->expireOverdue(now()));
        $this->assertSame(ClanBossRun::STATUS_EXPIRED, $run->fresh()->status);
        $this->assertSame(ClanBossRunStage::STATUS_EXPIRED, $state->fresh()->status);
        $this->assertSame(0, (int) $boss->fresh()->active);
        $this->assertSame(1, ClanBossRunStage::query()->where('clan_boss_run_id', $run->id)->count());
    }

    public function test_run_rating_sums_all_bosses_but_not_other_runs(): void
    {
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $otherRun = $run->replicate();
        $otherRun->status = ClanBossRun::STATUS_EXPIRED;
        $otherRun->save();

        foreach ([
            [$run->id, $fixtures['stage1']->id, $fixtures['leader']->id, 100, 2, true],
            [$run->id, $fixtures['stage2']->id, $fixtures['leader']->id, 200, 3, false],
            [$run->id, $fixtures['stage1']->id, $fixtures['member']->id, 400, 4, false],
            [$otherRun->id, $fixtures['stage1']->id, $fixtures['leader']->id, 9999, 99, true],
        ] as [$runId, $stageId, $userId, $damage, $hits, $rewarded]) {
            ClanBossContribution::query()->create([
                'clan_boss_run_id' => $runId, 'dungeon_stage_id' => $stageId, 'user_id' => $userId,
                'damage' => $damage, 'hits' => $hits, 'rewarded' => $rewarded,
            ]);
        }

        $rating = $service->ratingForRun($run->id);
        $this->assertCount(2, $rating);
        $this->assertSame($fixtures['member']->id, $rating[0]['user']->id);
        $this->assertSame(400, $rating[0]['damage']);
        $this->assertSame($fixtures['leader']->id, $rating[1]['user']->id);
        $this->assertSame(300, $rating[1]['damage']);
        $this->assertSame(5, $rating[1]['hits']);
        $this->assertTrue($rating[1]['rewarded']);
        $this->assertTrue($rating[1]['user']->relationLoaded('player'));
        $this->assertSame(100, (int) $service->myContribution($run->id, $fixtures['stage1']->id, $fixtures['leader']->id)->damage);

        $this->actingAs($fixtures['leader']);
        $this->app->instance(ClanBossService::class, $service);
        $data = $this->app->make(ClanBossController::class)->index($fixtures['structure']->id)->getData();
        $this->assertSame(100, (int) $data['myContribution']->damage);
        $this->assertSame(300, $data['myRunContribution']['damage']);
        $this->assertSame(5, $data['myRunContribution']['hits']);
        $this->assertCount(2, $data['rating']);

        // На новом боссе ещё нет ударов: общий вклад не исчезает.
        $run->update(['current_stage_id' => $fixtures['stage2']->id]);
        ClanBossContribution::query()->where('clan_boss_run_id', $run->id)->where('dungeon_stage_id', $fixtures['stage2']->id)->delete();
        $data = $this->app->make(ClanBossController::class)->index($fixtures['structure']->id)->getData();
        $this->assertNull($data['myContribution']);
        $this->assertSame(100, $data['myRunContribution']['damage']);
        $this->assertSame(2, $data['myRunContribution']['hits']);
    }

    public function test_run_rating_includes_every_participant_not_only_top_twenty(): void
    {
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);

        for ($i = 1; $i <= 25; $i++) {
            $user = User::query()->create(['name' => 'Участник '.$i, 'location_id' => $fixtures['structure']->location_id]);
            ClanBossContribution::query()->create([
                'clan_boss_run_id' => $run->id,
                'dungeon_stage_id' => $fixtures['stage1']->id,
                'user_id' => $user->id,
                'damage' => $i,
                'hits' => 1,
            ]);
        }

        $rating = $service->ratingForRun($run->id);
        $this->assertCount(25, $rating);
        $this->assertSame(25, $rating[0]['damage']);
        $this->assertSame(1, $rating[24]['damage']);
    }

    public function test_lair_stage_table_marks_killed_and_current_bosses_for_this_run(): void
    {
        $this->travelTo(now()->startOfSecond());
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $fixtures['leader']->player->setRelation('skills', new Collection);
        $this->actingAs($fixtures['leader']);
        $this->app->instance(ClanBossService::class, $service);
        $controller = $this->app->make(ClanBossController::class);

        $html = $controller->index($fixtures['structure']->id)->render();
        $this->assertStringNotContainsString('КД клана', $html);
        $this->assertStringNotContainsString('Окно босса', $html);
        $this->assertStringNotContainsString('Босс исчезнет:', $html);
        $this->assertSame('Текущий', $this->stageStatusText($html, $fixtures['stage1']->id));
        $this->assertSame('Не открыт', $this->stageStatusText($html, $fixtures['stage2']->id));
        $this->assertSame(1, preg_match('/data-stage-duration="'.$fixtures['stage1']->id.'"[^>]*>\s*—\s*<\/td>/', $html));

        $this->travel(12)->minutes();
        $boss = $this->currentBoss($run);
        $state = $service->assertCanHit($fixtures['leader'], $boss);
        $service->recordHit($fixtures['leader'], $state, 1000);
        $boss->update(['hp_now' => 0]);
        $service->handleStageKill($boss, $fixtures['leaderPlayer'], null, new AttackResultDTO);

        // Состояние другого похода не должно подменять текущий результат.
        $oldRun = $run->replicate();
        $oldRun->status = ClanBossRun::STATUS_EXPIRED;
        $oldRun->save();
        ClanBossRunStage::query()->create([
            'clan_boss_run_id' => $oldRun->id,
            'dungeon_stage_id' => $fixtures['stage1']->id,
            'hp_max' => 1000,
            'status' => ClanBossRunStage::STATUS_EXPIRED,
        ]);

        $view = $controller->index($fixtures['structure']->id);
        $this->assertCount(2, $view->getData()['stageStates']);
        $html = $view->render();
        $this->assertSame('Повержен', $this->stageStatusText($html, $fixtures['stage1']->id));
        $this->assertSame('Текущий', $this->stageStatusText($html, $fixtures['stage2']->id));
        $this->assertSame(1, preg_match('/data-stage-duration="'.$fixtures['stage1']->id.'"[^>]*>\s*12 минут\s*<\/td>/', $html));
        $this->assertSame(1, preg_match('/data-stage-duration="'.$fixtures['stage2']->id.'"[^>]*>\s*—\s*<\/td>/', $html));
        $this->assertSame($fixtures['stage2']->id, $view->getData()['currentState']->dungeon_stage_id);
    }

    public function test_lair_stage_table_shows_timeout_and_no_active_run_states(): void
    {
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $state = ClanBossRunStage::query()->where('clan_boss_run_id', $run->id)->firstOrFail();
        $fixtures['leader']->player->setRelation('skills', new Collection);
        $this->actingAs($fixtures['leader']);
        $this->app->instance(ClanBossService::class, $service);
        $controller = $this->app->make(ClanBossController::class);

        foreach ([ClanBossRunStage::STATUS_SKIPPED, ClanBossRunStage::STATUS_EXPIRED] as $status) {
            $state->update(['status' => $status]);
            $html = $controller->index($fixtures['structure']->id)->render();
            $this->assertSame('Время вышло', $this->stageStatusText($html, $fixtures['stage1']->id));
        }

        $run->update(['status' => ClanBossRun::STATUS_EXPIRED]);
        $view = $controller->index($fixtures['structure']->id);
        $this->assertTrue($view->getData()['stageStates']->isEmpty());
        $html = $view->render();
        $this->assertSame('—', $this->stageStatusText($html, $fixtures['stage1']->id));
        $this->assertSame('—', $this->stageStatusText($html, $fixtures['stage2']->id));
    }

    private function stageStatusText(string $html, int $stageId): string
    {
        $this->assertSame(1, preg_match('/<td[^>]*data-stage-status="'.$stageId.'"[^>]*>(.*?)<\/td>/s', $html, $matches));

        return trim(strip_tags($matches[1]));
    }

    public function test_kill_distributes_full_gold_budget_proportionally_to_eligible_damage(): void
    {
        [$service, $fixtures] = $this->makeService();
        $fixtures['stage1']->update(['gold_budget' => 1000]);
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $stageState = ClanBossRunStage::query()->where('clan_boss_run_id', $run->id)->firstOrFail();

        ClanBossContribution::query()->create([
            'clan_boss_run_id' => $run->id,
            'dungeon_stage_id' => $fixtures['stage1']->id,
            'user_id' => $fixtures['leader']->id,
            'damage' => 600,
            'hits' => 1,
        ]);
        ClanBossContribution::query()->create([
            'clan_boss_run_id' => $run->id,
            'dungeon_stage_id' => $fixtures['stage1']->id,
            'user_id' => $fixtures['member']->id,
            'damage' => 400,
            'hits' => 1,
        ]);

        $summary = $fixtures['rewardService']->grantForStage($run, $stageState, $fixtures['boss1'], $fixtures['leader']);

        // Помимо бюджета данжа каждому начисляется обычная награда в 50 монет.
        $this->assertSame(650, (int) $fixtures['leader']->fresh()->money);
        $this->assertSame(450, (int) $fixtures['member']->fresh()->money);
        $this->assertSame(1000, $summary['gold_granted']);
        $receipt = ClanBossRewardReceipt::where('user_id', $fixtures['leader']->id)->firstOrFail();
        $this->assertSame(650, $receipt->rewards[0]['amount']);
        $this->assertSame('boss_killed', $receipt->reason);
        $this->assertSame('Лидер', $receipt->recipient_name);
        $this->assertSame('Босс 1', $receipt->bossName());
        $this->assertCount(3, (new ClanBossRewardRecorder)->history($run->id));

        $chat = Mockery::mock(ChatService::class);
        $chat->shouldReceive('sendSystemToUser')->once()->withArgs(function ($user, $message) use ($fixtures): bool {
            $html = app(ChatMessageTranslator::class)->render($message, 'ru');
            $uk = app(ChatMessageTranslator::class)->render($message, 'uk');

            return $user->id === $fixtures['leader']->id && str_contains($html, '+650 монет') && str_contains($uk, 'Нагорода за шкоду босу');
        })->andReturn(new ChatMessage);
        (new SendClanBossRewardMessage($chat, app(PlayerNotificationService::class)))->handle(new ClanBossRewardGranted($receipt->id));
        $this->assertNotNull($receipt->fresh()->notified_at);
        (new SendClanBossRewardMessage($chat, app(PlayerNotificationService::class)))->handle(new ClanBossRewardGranted($receipt->id));

        // With the notification migration applied, offline recipients get a durable receipt instead of an expiring chat row.
        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
        });
        (require database_path('migrations/2026_10_17_000009_create_player_notifications_table.php'))->up();
        Event::fake([PlayerNotificationAvailable::class]);
        $receipt->refresh()->update(['notified_at' => null]);
        $listener = new SendClanBossRewardMessage($chat, app(PlayerNotificationService::class));
        $listener->handle(new ClanBossRewardGranted($receipt->id));
        $listener->handle(new ClanBossRewardGranted($receipt->id));
        $notification = PlayerNotification::query()->sole();
        $this->assertSame((int) $fixtures['leader']->id, (int) $notification->user_id);
        $this->assertSame('clan_boss_reward:'.$receipt->id, $notification->deduplication_key);
        $this->assertNull($notification->read_at);
        $this->assertNull($notification->chat_message_id);
        $this->assertSame(650, (int) $fixtures['leader']->fresh()->money);
        Event::assertDispatchedTimes(PlayerNotificationAvailable::class, 1);

        DB::table('users')->where('id', $fixtures['leader']->id)->update(['name' => 'Другой ник']);
        $this->assertSame('Лидер', $receipt->fresh()->recipient_name);
    }

    public function test_unfinished_stage_pays_proportional_gold_to_every_damage_contributor(): void
    {
        [$service, $fixtures] = $this->makeService();
        $fixtures['stage1']->update(['gold_budget' => 1000]);
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);

        $leaderContribution = ClanBossContribution::query()->create([
            'clan_boss_run_id' => $run->id,
            'dungeon_stage_id' => $fixtures['stage1']->id,
            'user_id' => $fixtures['leader']->id,
            'damage' => 200,
            'hits' => 1,
        ]);
        $memberContribution = ClanBossContribution::query()->create([
            'clan_boss_run_id' => $run->id,
            'dungeon_stage_id' => $fixtures['stage1']->id,
            'user_id' => $fixtures['member']->id,
            'damage' => 5,
            'hits' => 1,
        ]);
        $stageState = ClanBossRunStage::query()->where('clan_boss_run_id', $run->id)->firstOrFail();

        $granted = $fixtures['rewardService']->grantGoldForUnfinishedStage($run, $stageState);

        $this->assertSame(205, $granted);
        $this->assertSame(200, (int) $fixtures['leader']->fresh()->money);
        $this->assertSame(5, (int) $fixtures['member']->fresh()->money);
        $this->assertTrue((bool) $leaderContribution->fresh()->rewarded);
        $this->assertTrue((bool) $memberContribution->fresh()->rewarded);
        $receipt = ClanBossRewardReceipt::where('user_id', $fixtures['member']->id)->firstOrFail();
        $this->assertSame(5, $receipt->rewards[0]['amount']);
        $this->assertSame('run_expired', $receipt->reason);
        $this->assertSame(0, $fixtures['rewardService']->grantGoldForUnfinishedStage($run, $stageState));
        $this->assertSame(3, ClanBossRewardReceipt::count());
    }

    public function test_reward_receipts_capture_diamonds_experience_and_actual_item_destination(): void
    {
        [$service, $fixtures] = $this->makeService();
        $item = ShareItem::query()->forceCreate(['name' => 'Клановый кристалл', 'rarity' => 'rare']);
        foreach ([
            ['type' => DungeonRewardType::DIAMOND, 'amount' => 7, 'item' => null],
            ['type' => DungeonRewardType::EXPERIENCE, 'amount' => 100, 'item' => null],
            ['type' => DungeonRewardType::ITEM, 'amount' => 2, 'item' => $item->id],
        ] as $reward) {
            DungeonReward::create([
                'dungeon_id' => $fixtures['dungeon']->id, 'type' => $reward['type'],
                'share_item_id' => $reward['item'], 'amount_min' => $reward['amount'], 'amount_max' => $reward['amount'], 'drop_chance' => 100,
            ]);
        }
        $fixtures['experienceService']->shouldReceive('calculateGain')->once()->andReturn(150);
        // Нет кланового хранилища: награда данжа фактически уходит в рюкзак.
        $fixtures['rewardBackpack']->shouldReceive('addItemByShareItem')->once()->andReturn(new Backpack);
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $boss = $this->currentBoss($run);
        $state = $service->assertCanHit($fixtures['leader'], $boss);
        $service->recordHit($fixtures['leader'], $state, 1000);
        $boss->update(['hp_now' => 0]);
        $service->handleStageKill($boss, $fixtures['leaderPlayer'], null, new AttackResultDTO);

        $receipt = ClanBossRewardReceipt::where('user_id', $fixtures['leader']->id)->firstOrFail();
        $rewards = collect($receipt->rewards)->keyBy('type');
        $this->assertSame(7, $rewards['diamond']['amount']);
        $this->assertSame(7, (int) $fixtures['leader']->fresh()->diamond);
        $this->assertSame(150, $rewards['experience']['amount']);
        $this->assertSame(150, (int) $fixtures['leaderPlayer']->fresh()->exp);
        $this->assertSame(2, $rewards['item']['amount']);
        $this->assertSame('Клановый кристалл', $rewards['item']['item_name']['ru']);
        $this->assertSame('rare', $rewards['item']['rarity']);
        $html = view('clan::partials.boss-reward-receipts', ['rewardReceipts' => (new ClanBossRewardRecorder)->history($run->id)])->render();
        $this->assertStringContainsString('150 опыта', $html);
        $this->assertStringContainsString('Клановый кристалл', $html);
    }

    public function test_rolled_back_rewards_do_not_leave_receipts_or_send_notifications(): void
    {
        [$service, $fixtures] = $this->makeService();
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $state = ClanBossRunStage::where('clan_boss_run_id', $run->id)->firstOrFail();
        ClanBossContribution::create([
            'clan_boss_run_id' => $run->id, 'dungeon_stage_id' => $state->dungeon_stage_id,
            'user_id' => $fixtures['leader']->id, 'damage' => 1000, 'hits' => 1,
        ]);
        DB::beginTransaction();
        $fixtures['rewardService']->grantForStage($run, $state, $fixtures['boss1'], $fixtures['leader']);
        $this->assertSame(2, ClanBossRewardReceipt::count());
        Event::assertNotDispatched(ClanBossRewardGranted::class);
        DB::rollBack();
        $this->assertSame(0, ClanBossRewardReceipt::count());
        $this->assertSame(0, (int) $fixtures['leader']->fresh()->money);
        Event::assertNotDispatched(ClanBossRewardGranted::class);
    }

    public function test_boss_drop_receipt_and_clan_notice_describe_the_actual_warehouse_reward(): void
    {
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            foreach (['upgrade_lvl', 'upgrade_pity', 'upgrade_fail_streak', 'socket_count', 'rune_slot_count', 'additional_attack', 'count_use', 'is_open'] as $column) {
                $table->integer($column)->default(0);
            }
            $table->timestamps();
        });
        Schema::create('clan_warehouses', function (Blueprint $table): void {
            $table->id();
            foreach (['clan_id', 'structure_id', 'depositor_user_id', 'item_id', 'count'] as $column) {
                $table->unsignedBigInteger($column);
            }
            $table->timestamps();
        });
        Schema::create('clan_warehouse_logs', function (Blueprint $table): void {
            $table->id();
            foreach (['clan_id', 'user_id', 'structure_id', 'item_id', 'count'] as $column) {
                $table->unsignedBigInteger($column);
            }
            $table->string('action');
            $table->timestamps();
        });
        [$service, $fixtures] = $this->makeService();
        Structure::forceCreate(['type' => Structure::TYPE_CLAN_WAREHOUSE, 'name' => 'Хранилище']);
        $item = ShareItem::forceCreate(['name' => 'Редкий трофей', 'rarity' => 'epic']);
        DB::table('monster_has_items')->insert([
            'monster_id' => $fixtures['boss1']->id, 'share_item_id' => $item->id,
            'drop_chance' => 100, 'min_count' => 2, 'max_count' => 2,
        ]);
        $run = $service->openRun($fixtures['structure'], $fixtures['leader']);
        $boss = $this->currentBoss($run);
        $service->recordHit($fixtures['leader'], $service->assertCanHit($fixtures['leader'], $boss), 1000);
        $boss->update(['hp_now' => 0]);
        $service->handleStageKill($boss, $fixtures['leaderPlayer'], null, new AttackResultDTO);
        $receipt = ClanBossRewardReceipt::where('recipient_type', 'clan')->firstOrFail();
        $this->assertSame(2, $receipt->rewards[0]['amount']);
        $this->assertSame('Редкий трофей', $receipt->rewards[0]['item_name']['ru']);
        $this->assertSame('epic', $receipt->rewards[0]['rarity']);
        $this->assertSame(2, (int) DB::table('clan_warehouses')->sum('count'));

        $chat = Mockery::mock(ChatService::class);
        $chat->shouldReceive('sendClanNotice')->once()->withArgs(function ($clanId, $message) use ($fixtures, $item): bool {
            $text = app(ChatMessageTranslator::class)->render($message, 'ru');

            return $clanId === $fixtures['clan']->id && str_contains($text, '[[share_item_'.$item->id.']] ×2 (в клановое хранилище)');
        })->andReturn(new ChatMessage);
        (new SendClanBossRewardMessage($chat, app(PlayerNotificationService::class)))->handle(new ClanBossRewardGranted($receipt->id));

        DB::table('share_items')->where('id', $item->id)->update(['name' => '{"ru":"Изменённое имя"}']);
        $this->assertSame('Редкий трофей', $receipt->fresh()->rewards[0]['item_name']['ru']);
    }

    private function currentBoss(ClanBossRun $run): MonsterOnLocation
    {
        $state = ClanBossRunStage::query()
            ->where('clan_boss_run_id', $run->id)
            ->where('dungeon_stage_id', $run->current_stage_id)
            ->firstOrFail();

        return MonsterOnLocation::query()->findOrFail($state->monster_on_location_id);
    }

    /** @return array{0: ClanBossService, 1: array<string, mixed>} */
    private function makeService(): array
    {
        $location = Location::query()->forceCreate(['name' => 'Локация 28']);

        $dungeon = Dungeon::query()->create([
            'name' => 'Логово',
            'type' => DungeonType::TOWER,
            'is_active' => true,
            'is_clan_boss' => true,
            'min_clan_days' => 3,
            'min_damage' => 0,
            'time_limit_seconds' => 3600,
            'clan_boss_hit_batch_size' => 10,
            'clan_boss_hit_batch_cooldown_seconds' => 21600,
            'min_level' => 1,
        ]);

        $stage1 = DungeonStage::query()->create([
            'dungeon_id' => $dungeon->id,
            'number' => 1,
            'name' => 'Слабый',
            'time_limit_seconds' => 1800,
            'completion_type' => 'kill_all',
            'spawn_type' => 'fixed',
            'total_monsters' => 1,
            'max_hits_per_user' => 2,
        ]);
        $stage2 = DungeonStage::query()->create([
            'dungeon_id' => $dungeon->id,
            'number' => 2,
            'name' => 'Сильный',
            'time_limit_seconds' => 1800,
            'completion_type' => 'kill_all',
            'spawn_type' => 'fixed',
            'total_monsters' => 1,
        ]);

        $boss1 = Monster::query()->create(['name' => 'Босс 1', 'hp' => 1000, 'aggression' => 0, 'is_boss' => true, 'exp' => 100]);
        $boss2 = Monster::query()->create(['name' => 'Босс 2', 'hp' => 2000, 'aggression' => 0, 'is_boss' => true, 'exp' => 200]);
        $stage1->monsters()->create(['monster_id' => $boss1->id, 'quantity' => 1, 'weight' => 1]);
        $stage2->monsters()->create(['monster_id' => $boss2->id, 'quantity' => 1, 'weight' => 1]);

        DungeonReward::query()->create([
            'dungeon_id' => $dungeon->id,
            'type' => DungeonRewardType::GOLD,
            'amount_min' => 50,
            'amount_max' => 50,
            'drop_chance' => 100,
        ]);

        $structure = Structure::query()->forceCreate([
            'type' => 'clan_boss',
            'name' => 'Логово',
            'location_id' => $location->id,
            'dungeon_id' => $dungeon->id,
        ]);

        $leader = User::query()->create(['name' => 'Лидер', 'location_id' => $location->id]);
        $member = User::query()->create(['name' => 'Боец', 'location_id' => $location->id]);
        $leaderPlayer = Player::query()->forceCreate(['user_id' => $leader->id, 'exp' => 0, 'lvl' => 1]);
        $memberPlayer = Player::query()->forceCreate(['user_id' => $member->id, 'exp' => 0, 'lvl' => 1]);
        $leader->forceFill(['player_id' => $leaderPlayer->id])->save();
        $member->forceFill(['player_id' => $memberPlayer->id])->save();

        $clan = Clan::query()->create(['name' => 'Клан', 'owner_id' => $leader->id, 'lvl' => 1, 'experience' => 0, 'treasury' => 0, 'warehouse_capacity' => 50]);
        $role = ClanRole::query()->create(['clan_id' => $clan->id, 'name' => 'Лидер', 'permissions' => 0, 'is_leader' => true, 'is_default' => false]);
        $memberRole = ClanRole::query()->create(['clan_id' => $clan->id, 'name' => 'Боец', 'permissions' => ClanPermission::START_CLAN_BOSS->bit(), 'is_leader' => false, 'is_default' => true]);
        ClanMember::query()->forceCreate([
            'clan_id' => $clan->id, 'user_id' => $leader->id, 'role_id' => $role->id,
            'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10),
        ]);
        ClanMember::query()->forceCreate([
            'clan_id' => $clan->id, 'user_id' => $member->id, 'role_id' => $memberRole->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $rewardBackpack = Mockery::mock(BackpackService::class);
        $experienceService = Mockery::mock(ExperienceService::class);
        $rewardService = new ClanBossRewardService(
            $rewardBackpack,
            $experienceService,
            new ClanExperienceService(new ClanLevelService),
            new ClanLogService,
            new ClanBossRewardRecorder,
        );
        $service = new ClanBossService(Mockery::mock(BackpackService::class), $rewardService);

        return [$service, [
            'structure' => $structure,
            'leader' => $leader,
            'leaderPlayer' => $leaderPlayer,
            'member' => $member,
            'stage1' => $stage1,
            'stage2' => $stage2,
            'dungeon' => $dungeon,
            'clan' => $clan,
            'boss1' => $boss1,
            'rewardBackpack' => $rewardBackpack,
            'rewardService' => $rewardService,
            'experienceService' => $experienceService,
            'battleId' => null,
        ]];
    }

    private function createSchema(): void
    {
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('dungeon_id')->nullable();
            $table->timestamps();
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('dungeon_run_id')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('rounds')->default(0);
            $table->json('boss_metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('prev_location_id')->nullable();
            $table->integer('money')->default(0);
            $table->integer('diamond')->default(0);
            $table->boolean('is_admin')->default(false);
            $table->integer('warehouse_count')->default(50);
            $table->integer('bag_count')->default(25);
            $table->integer('slot_count')->default(3);
            $table->timestamps();
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('lvl')->default(1);
            $table->bigInteger('exp')->default(0);
            $table->float('experience_multiplier')->default(1);
            $table->integer('reputation_rating')->default(0);
            $table->timestamps();
        });
        Schema::create('clans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('lvl')->default(1);
            $table->decimal('experience', 18, 2)->default(0);
            $table->unsignedBigInteger('treasury')->default(0);
            $table->unsignedInteger('warehouse_capacity')->default(50);
            $table->unsignedBigInteger('owner_id');
            $table->timestamps();
        });
        Schema::create('clan_levels', function (Blueprint $table): void {
            $table->unsignedInteger('level')->primary();
            $table->unsignedBigInteger('experience_required');
        });
        DB::table('clan_levels')->insert(['level' => 1, 'experience_required' => 0]);
        Schema::create('clan_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_id');
            $table->string('name');
            $table->unsignedBigInteger('permissions')->default(0);
            $table->boolean('is_leader')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
        Schema::create('clan_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedInteger('points')->default(0);
            $table->decimal('experience_contributed', 18, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('clan_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('details')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeons', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_clan_boss')->default(false);
            $table->unsignedTinyInteger('min_clan_days')->default(3);
            $table->unsignedBigInteger('min_damage')->default(0);
            $table->unsignedBigInteger('entry_share_item_id')->nullable();
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->unsignedSmallInteger('clan_boss_hit_batch_size')->default(10);
            $table->unsignedInteger('clan_boss_hit_batch_cooldown_seconds')->default(21600);
            $table->unsignedTinyInteger('min_level')->default(1);
            $table->timestamps();
        });
        Schema::create('dungeon_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->integer('number');
            $table->string('name');
            $table->integer('time_limit_seconds')->nullable();
            $table->string('completion_type');
            $table->string('spawn_type');
            $table->integer('total_monsters');
            $table->unsignedBigInteger('next_stage_id')->nullable();
            $table->unsignedInteger('max_hits_per_user')->nullable();
            $table->unsignedInteger('personal_cooldown_seconds')->nullable();
            $table->unsignedInteger('clan_cooldown_seconds')->nullable();
            $table->unsignedInteger('gold_budget')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeon_stage_monsters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('monster_id');
            $table->integer('quantity');
            $table->integer('weight');
            $table->timestamps();
        });
        Schema::create('dungeon_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->string('type');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->integer('amount_min');
            $table->integer('amount_max');
            $table->float('drop_chance');
            $table->timestamps();
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('hp');
            $table->integer('aggression')->default(0);
            $table->integer('exp')->default(0);
            $table->boolean('is_boss')->default(false);
            $table->timestamps();
        });
        Schema::create('monster_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('share_item_id');
            $table->double('drop_chance', 8, 7);
            $table->smallInteger('min_count')->default(1);
            $table->smallInteger('max_count')->default(1);
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('rarity')->nullable();
            $table->unsignedInteger('count_use')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_auction_sellable')->default(false);
            $table->boolean('is_give')->default(true);
            $table->boolean('is_clan_warehouse_allowed')->default(true);
            $table->boolean('is_droppable')->default(true);
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_slot_usable')->default(false);
            $table->boolean('is_use')->default(false);
            $table->boolean('is_weight')->default(true);
            $table->unsignedInteger('break_crystal')->default(0);
            $table->unsignedInteger('price')->default(0);
            $table->timestamps();
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->unsignedBigInteger('clan_boss_stage_state_id')->nullable();
            $table->unsignedBigInteger('monster_id');
            $table->integer('hp_now');
            $table->integer('hp_max');
            $table->boolean('active')->default(true);
            $table->integer('aggression')->nullable();
            $table->boolean('is_drop_money')->default(0);
            $table->integer('current_phase')->default(1);
            $table->timestamps();
        });
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('dungeon_id')->nullable();
            $table->timestamps();
        });
        Schema::create('boss_defeat_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->unsignedBigInteger('player_id');
            $table->integer('turns_taken')->default(0);
            $table->json('mechanics_triggered')->nullable();
            $table->integer('final_phase')->default(1);
            $table->timestamps();
        });
        Schema::create('clan_boss_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('clan_id');
            $table->unsignedBigInteger('opened_by_user_id');
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
        Schema::create('clan_boss_run_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_boss_run_id');
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('monster_on_location_id')->nullable();
            $table->unsignedBigInteger('hp_max');
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('killed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('clan_boss_contributions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_boss_run_id');
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('damage')->default(0);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('rewarded')->default(false);
            $table->timestamps();
        });
        Schema::create('clan_boss_player_attack_states', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_boss_run_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('hits_in_batch')->default(0);
            $table->timestamp('cooldown_until')->nullable();
            $table->timestamps();
            $table->unique(['clan_boss_run_id', 'user_id']);
        });
        Schema::create('clan_boss_cooldowns', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('clan_id');
            $table->timestamp('available_at')->nullable();
            $table->timestamps();
        });
    }
}
